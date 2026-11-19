
<?php

namespace App\Jobs;

use App\Models\Website;
use App\Models\WebsiteProvisioningRun;
use App\Services\Provisioning\LampRecipe;
use App\Services\Provisioning\ServerSshService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProvisionWebsiteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 1800;
    public bool $failOnTimeout = true;

    public function __construct(
        public int $runId
    ) {
        $this->onQueue('provisioning');
    }

    public function middleware(): array
    {
        $run = WebsiteProvisioningRun::findOrFail($this->runId);

        return [
            (new WithoutOverlapping(
                'zeitops-server-' . $run->server_id
            ))
                ->shared()
                ->releaseAfter(30)
                ->expireAfter(1900),
        ];
    }

    public function handle(ServerSshService $servers): void
    {
        $run = WebsiteProvisioningRun::findOrFail($this->runId);
        $website = Website::findOrFail($run->website_id);

        if (
            $website->server_id !== $run->server_id ||
            !LampRecipe::supported(
                $website->stack,
                (string) ($website->runtime_version ?: $website->php_version)
            )
        ) {
            $this->recordFailure(
                $run,
                'Website configuration changed or is unsupported.'
            );
            return;
        }

        try {
            $this->setStatus(
                $run,
                'running',
                'Establishing a verified SSH connection'
            );

            $ssh = $servers->connect(
                $run->server_id,
                (int) $website->user_id
            );

            /*
             * STEP 1 — Inspect.
             *
             * Fail closed on unsupported OS, no sudo access,
             * or an existing Nginx service that could conflict.
             */

            $this->beginTask(
                $run,
                'inspect',
                'Inspecting Ubuntu and permissions'
            );

            $os = $servers->execute(
                $ssh,
                '. /etc/os-release && printf "%s:%s" "$ID" "$VERSION_ID"'
            );

            if ($os !== 'ubuntu:24.04') {
                throw new RuntimeException(
                    'Only Ubuntu 24.04 is currently supported.'
                );
            }

            $servers->execute($ssh, 'sudo -n true');

            // Do not modify a VPS with an existing Nginx setup.
            $nginx = $servers->execute(
                $ssh,
                'if command -v nginx >/dev/null 2>&1; then echo present; else echo absent; fi'
            );

            if ($nginx === 'present') {
                throw new RuntimeException(
                    'Nginx is installed. Manual review is required before installing Apache.'
                );
            }

            // Baseline resources: adjust based on workloads.
            $memory = (int) $servers->execute(
                $ssh,
                "awk '/MemTotal:/ {print int(\$2 / 1024)}' /proc/meminfo"
            );

            $disk = (int) $servers->execute(
                $ssh,
                "df -Pm / | awk 'NR==2 {print \$4}'"
            );

            if ($memory < 1024 || $disk < 2048) {
                throw new RuntimeException(
                    'Insufficient RAM or free disk space for this installation.'
                );
            }

            $this->finishTask(
                $run,
                'inspect',
                'Ubuntu 24.04, permissions and resources checked'
            );

            /*
             * STEP 2 — Refresh package information.
             */

            $this->beginTask(
                $run,
                'packages',
                'Refreshing Ubuntu package information'
            );

            $servers->execute(
                $ssh,
                'sudo -n env DEBIAN_FRONTEND=noninteractive apt-get update -qq',
                600
            );

            $this->finishTask(
                $run,
                'packages',
                'Package information refreshed'
            );

            /*
             * STEP 3 — Apache
             */

            $this->beginTask(
                $run,
                'apache',
                'Installing Apache'
            );

            $this->installPackages(
                $servers,
                $ssh,
                ['apache2']
            );

            $servers->execute(
                $ssh,
                'apache2 -v'
            );

            $this->finishTask(
                $run,
                'apache',
                'Apache installed and verified'
            );

            /*
             * STEP 4 — PHP 8.3
             */

            $this->beginTask(
                $run,
                'php',
                'Installing PHP 8.3, PDO and extensions'
            );

            $this->installPackages(
                $servers,
                $ssh,
                [
                    'php8.3-cli',
                    'php8.3-fpm',
                    'php8.3-common',
                    'php8.3-mysql',
                    'php8.3-mbstring',
                    'php8.3-xml',
                    'php8.3-curl',
                    'php8.3-zip',
                    'php8.3-bcmath',
                    'php8.3-intl',
                ]
            );

            $servers->execute(
                $ssh,
                "php8.3 -r 'if (!extension_loaded(\"PDO\") || !extension_loaded(\"pdo_mysql\")) exit(1);'"
            );

            $this->finishTask(
                $run,
                'php',
                'PHP 8.3, PDO and pdo_mysql verified'
            );

            /*
             * STEP 5 — MySQL and tools.
             */

            $this->beginTask(
                $run,
                'database',
                'Installing MySQL, Composer and Git'
            );

            $this->installPackages(
                $servers,
                $ssh,
                [
                    'mysql-server',
                    'composer',
                    'git',
                    'unzip',
                    'curl',
                    'ca-certificates',
                ]
            );

            $servers->execute(
                $ssh,
                'mysql --version && composer --version && git --version'
            );

            $this->finishTask(
                $run,
                'database',
                'Database and development tools verified'
            );

            /*
             * STEP 6 — Final dependency verification.
             */

            $this->beginTask(
                $run,
                'verify',
                'Verifying installed LAMP components'
            );

            $servers->execute(
                $ssh,
                'dpkg-query -W -f="${Status}" apache2 | grep -qx "install ok installed"'
            );

            $servers->execute(
                $ssh,
                'dpkg-query -W -f="${Status}" php8.3-fpm | grep -qx "install ok installed"'
            );

            $servers->execute(
                $ssh,
                'dpkg-query -W -f="${Status}" mysql-server | grep -qx "install ok installed"'
            );

            $servers->execute(
                $ssh,
                'php8.3 -m | grep -qi "^pdo_mysql$"'
            );

            $this->finishTask(
                $run,
                'verify',
                'Required packages and PHP extensions verified'
            );

            /*
             * Complete server dependency preparation.
             * Do NOT set website status to active.
             */

            $run->refresh();

            $run->update([
                'status' => 'completed',
                'percentage' => 100,
                'current_operation' => 'LAMP dependencies ready',
                'message' => 'Server dependencies installed. Application deployment is the next stage.',
                'finished_at' => now(),
            ]);

            $this->appendLog(
                $run,
                'LAMP dependency preparation completed.'
            );

            // Website remains pending until deployment and health checks.

        } catch (Throwable $e) {
            $this->recordFailure(
                $run,
                'Server preparation failed. Review the provisioning logs.'
            );

            Log::error('ZeitOps provisioning failure', [
                'run_id' => $run->id,
                'website_id' => $website->id,
                'exception' => $e,
            ]);
        }
    }

    private function installPackages(
        ServerSshService $servers,
                         $ssh,
        array $packages
    ): void {
        $allowed = LampRecipe::packages();

        foreach ($packages as $package) {
            if (!in_array($package, $allowed, true)) {
                throw new RuntimeException(
                    'Unapproved package requested.'
                );
            }
        }

        $command = 'sudo -n env DEBIAN_FRONTEND=noninteractive ' .
            'apt-get install -y --no-install-recommends ' .
            implode(' ', $packages);

        $servers->execute($ssh, $command, 900);
    }

    private function setStatus(
        WebsiteProvisioningRun $run,
        string $status,
        string $operation
    ): void {
        $run->update([
            'status' => $status,
            'current_operation' => $operation,
            'started_at' => $run->started_at ?? now(),
        ]);
    }

    private function beginTask(
        WebsiteProvisioningRun $run,
        string $taskId,
        string $operation
    ): void {
        $run->refresh();

        $tasks = $run->tasks ?? [];

        foreach ($tasks as &$task) {
            if ($task['id'] === $taskId) {
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

        $this->appendLog($run, $operation);
    }

    private function finishTask(
        WebsiteProvisioningRun $run,
        string $taskId,
        string $detail
    ): void {
        $run->refresh();

        $tasks = $run->tasks ?? [];

        foreach ($tasks as &$task) {
            if ($task['id'] === $taskId) {
                $task['status'] = 'completed';
                $task['detail'] = $detail;
            }
        }
        unset($task);

        $completed = count(array_filter(
            $tasks,
            fn ($task) => $task['status'] === 'completed'
        ));

        $total = count($tasks);

        // Reserve 100% for successful final verification.
        $percentage = $total > 0
            ? min(99, (int) floor($completed / $total * 100))
            : 0;

        $run->update([
            'tasks' => $tasks,
            'percentage' => $percentage,
            'current_operation' => $detail,
            'message' => $detail,
        ]);

        $this->appendLog($run, $detail);
    }

    private function appendLog(
        WebsiteProvisioningRun $run,
        string $message
    ): void {
        $run->refresh();

        $logs = $run->logs ?? [];

        $logs[] = now()->format('H:i:s') . ' ' . $message;

        $run->update([
            'logs' => array_slice($logs, -100),
        ]);
    }

    private function recordFailure(
        WebsiteProvisioningRun $run,
        string $message
    ): void {
        $run->refresh();

        $tasks = $run->tasks ?? [];

        foreach ($tasks as &$task) {
            if ($task['status'] === 'running') {
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
            $this->recordFailure(
                $run,
                'Provisioning worker terminated unexpectedly.'
            );
        }
    }
}
