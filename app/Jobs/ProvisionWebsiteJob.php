<?php

namespace App\Jobs;

use App\Models\Website;
use App\Models\WebsiteProvisioningRun;
use App\Services\Provisioning\LampRecipe;
use App\Services\Provisioning\ServerSshService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Prepares SERVER DEPENDENCIES for a LAMP / PHP 8.3 website.
 * This is NOT application deployment: it does not clone Git, create
 * databases, configure a virtual host, or mark the website active.
 *
 * Requires the restricted helper installed by ServerBootstrapService.
 */
class ProvisionWebsiteJob implements ShouldQueue
{
    use Queueable;

    private const HELPER = '/usr/local/libexec/zeitops/provision';

    public int $tries = 20; // Allow lock contention to release and requeue.
    public int $timeout = 1800;
    public bool $failOnTimeout = true;

    public function __construct(public int $runId)
    {
        $this->onQueue('provisioning');
    }

    public function middleware(): array
    {
        $run = WebsiteProvisioningRun::findOrFail($this->runId);

        return [
            (new WithoutOverlapping('zeitops-server-' . $run->server_id))
                ->shared()
                ->releaseAfter(30)
                ->expireAfter(1900),
        ];
    }

    public function handle(ServerSshService $servers): void
    {
        $run = WebsiteProvisioningRun::findOrFail($this->runId);

        // A duplicate delivery must not repeat installations.
        if (in_array($run->status, ['completed', 'failed'], true)) {
            return;
        }

        $website = Website::findOrFail($run->website_id);

        try {
            if (
                (string) $website->server_id !== (string) $run->server_id ||
                !LampRecipe::supported(
                    (string) $website->stack,
                    (string) ($website->runtime_version ?: $website->php_version)
                )
            ) {
                throw new RuntimeException('Website stack or server configuration is unsupported.');
            }

            // The local status is an additional gate, NOT a substitute for
            // verifying privileged access on the remote VPS.
            $metadata = $this->loadServerMetadata(
                (string) $run->server_id,
                (int) $website->user_id
            );

            if (($metadata['provisioning_status'] ?? null) !== 'ready') {
                throw new RuntimeException('Server bootstrap is not ready. Authorize it from Manage Server first.');
            }

            $this->setStatus($run, 'running', 'Connecting to VPS via verified SSH');
            $ssh = $servers->connect((string) $run->server_id, (int) $website->user_id);

            // 1 / 6: Read-only checks before ANY privileged modifications.
            $this->beginTask($run, 'inspect', 'Inspecting Ubuntu, permissions and resources');

            $os = $servers->execute(
                $ssh,
                '. /etc/os-release && printf "%s:%s" "$ID" "$VERSION_ID"'
            );

            if ($os !== 'ubuntu:24.04') {
                throw new RuntimeException('Only Ubuntu 24.04 is currently supported.');
            }

            $rootUid = $this->helper($servers, $ssh, 'check', 30);
            if ($rootUid !== '0') {
                throw new RuntimeException('The ZeitOps provisioning helper is not running as root.');
            }

            // Do not install Apache over a server that already has Nginx.
            $nginx = $servers->execute(
                $ssh,
                'if command -v nginx >/dev/null 2>&1; then printf present; else printf absent; fi'
            );
            if ($nginx === 'present') {
                throw new RuntimeException('Nginx is already installed; LAMP provisioning requires review.');
            }

            $memory = (int) $servers->execute(
                $ssh,
                "awk '/MemTotal:/ {print int(\$2 / 1024)}' /proc/meminfo"
            );
            $disk = (int) $servers->execute(
                $ssh,
                "df -Pm / | awk 'NR==2 {print \$4}'"
            );

            // 1 GiB VPSs often report less than 1024 MiB usable memory.
            // This threshold is a minimal guard, not a production sizing claim.
            if ($memory < 768 || $disk < 2048) {
                throw new RuntimeException('Insufficient available VPS resources for LAMP installation.');
            }

            $this->finishTask($run, 'inspect', "Ubuntu 24.04; {$memory} MiB RAM; {$disk} MiB free disk; helper verified");

            // 2 / 6: Privileged commands are *only* fixed helper operations.
            $this->beginTask($run, 'packages', 'Updating Ubuntu package indexes');
            $this->helper($servers, $ssh, 'update', 600);
            $this->finishTask($run, 'packages', 'Package indexes updated');

            // 3 / 6: Apache.
            $this->beginTask($run, 'apache', 'Installing Apache web server');
            $this->helper($servers, $ssh, 'install-apache', 900);
            $servers->execute($ssh, 'apache2 -v');
            $this->finishTask($run, 'apache', 'Apache installed and verified');

            // 4 / 6: PHP and PDO.
            $this->beginTask($run, 'php', 'Installing PHP 8.3, PHP-FPM and PDO extensions');
            $this->helper($servers, $ssh, 'install-php83', 900);
            $servers->execute(
                $ssh,
                "php8.3 -r 'if (!extension_loaded(\"PDO\") || !extension_loaded(\"pdo_mysql\")) {exit(1);}'"
            );
            $this->finishTask($run, 'php', 'PHP 8.3, PDO and pdo_mysql verified');

            // 5 / 6: Database and build tools.
            $this->beginTask($run, 'database', 'Installing MySQL, Composer and Git');
            $this->helper($servers, $ssh, 'install-tools', 900);
            $servers->execute($ssh, 'mysql --version && composer --version && git --version');
            $this->finishTask($run, 'database', 'MySQL, Composer and Git verified');

            // 6 / 6: Verify package presence. Service configuration and
            // web traffic health will be handled in the deployment stage.
            $this->beginTask($run, 'verify', 'Verifying installed stack dependencies');
            foreach (['apache2', 'php8.3-fpm', 'mysql-server'] as $package) {
                // The package names come only from this fixed internal list.
                $servers->execute(
                    $ssh,
                    'dpkg-query -W -f="${Status}" ' . $package .
                    ' | grep -qx "install ok installed"'
                );
            }
            $servers->execute($ssh, 'php8.3 -m | grep -qi "^pdo_mysql$"');
            $this->finishTask($run, 'verify', 'All requested packages and extensions verified');

            $run->refresh();
            $run->update([
                'status' => 'completed',
                'percentage' => 100,
                'current_operation' => 'LAMP server dependencies ready',
                'message' => 'Server dependencies are ready. Git and website deployment are separate steps.',
                'error' => null,
                'finished_at' => now(),
            ]);

            $this->appendLog($run, 'Dependency provisioning completed successfully.');
            Log::channel('provisioning')->info('Website server dependencies ready', [
                'run_id' => $run->id,
                'website_id' => $website->id,
                'server_id' => $run->server_id,
            ]);

            // Never set Website::status to active here.
        } catch (Throwable $e) {
            $run->refresh();
            $operation = $run->current_operation ?: 'initialization';
            $this->recordFailure($run, 'Provisioning failed during: ' . $operation . '. Check server logs.');

            // Log operation identity; avoid recording remote output, credentials,
            // SSH private keys, or untrusted shell commands.
            Log::channel('provisioning')->error('Website provisioning failed', [
                'run_id' => $run->id,
                'website_id' => $website->id,
                'server_id' => $run->server_id,
                'operation' => $operation,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);
        }
    }

    private function helper(
        ServerSshService $servers,
                         $ssh,
        string $operation,
        int $timeout
    ): string {
        $allowed = ['check', 'update', 'install-apache', 'install-php83', 'install-tools'];
        if (!in_array($operation, $allowed, true)) {
            throw new RuntimeException('Unsupported privileged operation.');
        }

        return $servers->execute(
            $ssh,
            'sudo -n ' . self::HELPER . ' ' . $operation,
            $timeout
        );
    }

    private function loadServerMetadata(string $serverId, int $ownerId): array
    {
        if (!Str::isUuid($serverId)) {
            throw new RuntimeException('Invalid registered server ID.');
        }

        $path = storage_path("app/private/zeitops/servers/{$serverId}/server.json");
        if (!File::isFile($path)) {
            throw new RuntimeException('Registered server metadata is missing.');
        }

        $server = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (
            !is_array($server) ||
            ($server['id'] ?? null) !== $serverId ||
            (string) ($server['user_id'] ?? '') !== (string) $ownerId ||
            ($server['status'] ?? null) !== 'connected'
        ) {
            throw new RuntimeException('Server connection or ownership is invalid.');
        }

        return $server;
    }

    private function setStatus(WebsiteProvisioningRun $run, string $status, string $operation): void
    {
        $run->update([
            'status' => $status,
            'current_operation' => $operation,
            'started_at' => $run->started_at ?? now(),
        ]);
    }

    private function beginTask(WebsiteProvisioningRun $run, string $taskId, string $operation): void
    {
        $run->refresh();
        $tasks = $run->tasks ?? [];
        foreach ($tasks as &$task) {
            if (($task['id'] ?? null) === $taskId) {
                $task['status'] = 'running';
                $task['detail'] = $operation;
            }
        }
        unset($task);

        $run->update([
            'tasks' => $tasks,
            'current_operation' => $operation,
            'message' => $operation,
        ]);

        Log::channel('provisioning')->info('Provisioning task started', [
            'run_id' => $run->id,
            'task_id' => $taskId,
            'operation' => $operation,
        ]);
        $this->appendLog($run, $operation);
    }

    private function finishTask(WebsiteProvisioningRun $run, string $taskId, string $detail): void
    {
        $run->refresh();
        $tasks = $run->tasks ?? [];
        foreach ($tasks as &$task) {
            if (($task['id'] ?? null) === $taskId) {
                $task['status'] = 'completed';
                $task['detail'] = $detail;
            }
        }
        unset($task);

        $completed = count(array_filter(
            $tasks,
            fn (array $task): bool => ($task['status'] ?? null) === 'completed'
        ));
        $run->update([
            'tasks' => $tasks,
            'percentage' => count($tasks) ? min(99, (int) floor($completed * 100 / count($tasks))) : 0,
            'current_operation' => $detail,
            'message' => $detail,
        ]);

        Log::channel('provisioning')->info('Provisioning task completed', [
            'run_id' => $run->id,
            'task_id' => $taskId,
            'percentage' => $run->percentage,
        ]);
        $this->appendLog($run, $detail);
    }

    private function appendLog(WebsiteProvisioningRun $run, string $message): void
    {
        $run->refresh();
        $logs = $run->logs ?? [];
        $logs[] = now()->format('H:i:s') . ' ' . $message;
        $run->update(['logs' => array_slice($logs, -100)]);
    }

    private function recordFailure(WebsiteProvisioningRun $run, string $message): void
    {
        $run->refresh();
        $tasks = $run->tasks ?? [];
        foreach ($tasks as &$task) {
            if (($task['status'] ?? null) === 'running') {
                $task['status'] = 'failed';
                $task['detail'] = $message;
            }
        }
        unset($task);

        $run->update([
            'status' => 'failed',
            'tasks' => $tasks,
            'error' => $message,
            'message' => $message,
            'finished_at' => now(),
        ]);
        $this->appendLog($run, $message);
    }

    public function failed(?Throwable $exception): void
    {
        $run = WebsiteProvisioningRun::find($this->runId);
        if ($run && !in_array($run->status, ['completed', 'failed'], true)) {
            $this->recordFailure($run, 'Provisioning worker terminated unexpectedly.');
        }
    }
}
