<?php

namespace App\Jobs;

use App\Models\Website;
use App\Models\WebsiteDeployment;
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
 * First-release Laravel deployment executor for trusted/customer-owned VPSs.
 * Requires the separately installed, root-owned ZeitOps deployment helper.
 * Do not deploy mutually untrusted customers onto a shared OS without stronger isolation.
 */
final class DeployWebsiteJob implements ShouldQueue
{
    use Queueable;

    private const HELPER = '/usr/local/libexec/zeitops/deploy';

    public int $tries = 20;
    public int $timeout = 3600;
    public bool $failOnTimeout = true;

    public function __construct(public int $websiteId, public ?int $deploymentId = null)
    {
        $this->onQueue('deployments');
    }

    public function middleware(): array
    {
        $website = Website::findOrFail($this->websiteId);
        return [
            (new WithoutOverlapping('zeitops-server-' . $website->server_id))
                ->shared()->releaseAfter(45)->expireAfter(3700),
        ];
    }

    public function handle(ServerSshService $servers): void
    {
        $website = Website::findOrFail($this->websiteId);
        $serverId = (string) $website->server_id;
        $run = $this->deploymentId ? WebsiteDeployment::where('website_id', $website->id)->findOrFail($this->deploymentId) : null;
        if ($run && in_array($run->status, ['completed', 'failed'], true)) {
            return;
        }
        if ($run) {
            $run->update(['status' => 'running', 'stage' => 'validation', 'started_at' => now()]);
        }
        $previousStatus = (string) $website->status;
        $website->update(['status' => 'deploying']);

        try {
            if ($website->stack !== 'lamp' || $website->framework !== 'laravel') {
                throw new RuntimeException('Only Laravel on the LAMP/PHP 8.3 stack is supported.');
            }

            if ((string) ($website->php_version ?: '8.3') !== '8.3') {
                throw new RuntimeException('This release supports PHP 8.3 only.');
            }

            $url = $this->githubUrl((string) $website->repository_url);
            $branch = $this->branch((string) ($website->repository_branch ?: 'main'));
            $domain = $this->domain((string) $website->domain);
            $this->assertServerReady($serverId, (int) $website->user_id);

            Log::channel('provisioning')->info('Website deployment started', [
                'website_id' => $website->id, 'server_id' => $serverId,
                'branch' => $branch, 'domain' => $domain,
            ]);

            $run?->update(['stage' => 'connecting']);
            $ssh = $servers->connect($serverId, (int) $website->user_id);
            if ($servers->execute($ssh, 'id -un', 30) !== 'zeitops') {
                throw new RuntimeException('Remote SSH identity is not the ZeitOps management account.');
            }

            // Verify the branch through the existing management connection.
            $ref = 'refs/heads/' . $branch;
            $cmd = 'GIT_TERMINAL_PROMPT=0 GIT_ASKPASS=/bin/false ' .
                'git -c credential.helper= ls-remote --exit-code --heads ' .
                escapeshellarg($url) . ' ' . escapeshellarg($ref);
            $output = trim($servers->execute($ssh, $cmd, 120));
            if (!preg_match('/^([a-f0-9]{40})\s+' . preg_quote($ref, '/') . '$/D', $output, $m)) {
                throw new RuntimeException('Repository branch did not resolve to an expected commit.');
            }
            $commit = $m[1];
            $run?->update(['commit_sha' => $commit, 'stage' => 'deploying-on-vps']);

            // The root-owned helper rejects invalid args, never executes app code as root,
            // and uses an OS identity dedicated to this website.
            $args = [(string) $website->id, $domain, $url, $branch, $commit, $run?->run_migrations ? 'run' : 'skip'];
            $command = 'sudo -n ' . self::HELPER . ' ' .
                implode(' ', array_map('escapeshellarg', $args));
            $result = trim($servers->execute($ssh, $command, 3300));

            if (!str_contains($result, 'ZEITOPS_RELEASE_READY')) {
                throw new RuntimeException('The deployment helper did not confirm release activation.');
            }

            $publicHttpsReady = str_contains($result, 'ZEITOPS_PUBLIC_HTTPS_READY');
            $website->update([
                'status' => $publicHttpsReady ? 'active' : 'deployed',
                'last_deployed_at' => now(),
            ]);

            $run?->update(['status' => 'completed', 'stage' => $publicHttpsReady ? 'https-ready' : 'http-ready', 'finished_at' => now(), 'error' => null]);

            Log::channel('provisioning')->info('Website release deployed', [
                'website_id' => $website->id, 'domain' => $domain,
                'commit' => $commit, 'https_ready' => $publicHttpsReady,
                'status' => $publicHttpsReady ? 'active' : 'deployed',
            ]);
        } catch (Throwable $e) {
            // A failed redeploy can restore an existing healthy release on the VPS.
            // Preserve its last known panel status; mark the attempt failed separately.
            $website->update(['status' => in_array($previousStatus, ['active', 'deployed'], true) ? $previousStatus : 'failed']);
            $run?->update(['status' => 'failed', 'stage' => 'failed', 'error' => $e instanceof RuntimeException ? $this->safeError($e->getMessage()) : 'Unexpected deployment failure. Check the VPS deployment log.', 'finished_at' => now()]);
            Log::channel('provisioning')->error('Website deployment failed', [
                'website_id' => $website->id, 'server_id' => $serverId,
                'error_class' => get_class($e),
                // Service errors are intentionally generic; no SSH output or secrets.
                'reason' => $e instanceof RuntimeException ? $e->getMessage() : 'Unexpected deployment failure.',
            ]);
            throw $e;
        }
    }

    private function safeError(string $message): string
    {
        return mb_substr(preg_replace('/[\r\n]+/', ' ', $message), 0, 350);
    }

    public function failed(?Throwable $exception): void
    {
        $website = Website::find($this->websiteId);
        if ($this->deploymentId) {
            WebsiteDeployment::whereKey($this->deploymentId)->whereIn('status', ['queued', 'running'])->update([
                'status' => 'failed', 'stage' => 'failed', 'error' => 'Deployment worker failed or timed out.', 'finished_at' => now(),
            ]);
        }
        if ($website && $website->status === 'deploying') {
            $website->update(['status' => 'failed']);
        }
    }

    private function githubUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('~^https://github\.com/([A-Za-z0-9-]{1,39})/([A-Za-z0-9._-]{1,100})(?:\.git)?/?$~D', $url, $m)) {
            throw new RuntimeException('Only public GitHub HTTPS repository URLs are supported.');
        }
        $repo = preg_replace('/\.git$/', '', $m[2]);
        if ($repo === '' || $repo === '.' || $repo === '..' || str_starts_with($repo, '-')) {
            throw new RuntimeException('Invalid GitHub repository name.');
        }
        return 'https://github.com/' . $m[1] . '/' . $repo . '.git';
    }

    private function branch(string $branch): string
    {
        if (strlen($branch) > 150 ||
            !preg_match('~^[A-Za-z0-9][A-Za-z0-9._/-]*$~D', $branch) ||
            str_contains($branch, '..') || str_contains($branch, '//') ||
            str_ends_with($branch, '/') || str_ends_with($branch, '.') ||
            str_ends_with($branch, '.lock')) {
            throw new RuntimeException('Invalid repository branch.');
        }
        return $branch;
    }

    private function domain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        if (strlen($domain) > 253 || !preg_match(
                '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D',
                $domain
            )) {
            throw new RuntimeException('A valid public domain is required.');
        }
        return $domain;
    }

    private function assertServerReady(string $serverId, int $ownerId): void
    {
        if (!Str::isUuid($serverId)) {
            throw new RuntimeException('Invalid registered server ID.');
        }
        $path = storage_path("app/private/zeitops/servers/{$serverId}/server.json");
        if (!File::isFile($path)) {
            throw new RuntimeException('Server metadata missing.');
        }
        $meta = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($meta) || ($meta['id'] ?? null) !== $serverId ||
            (string) ($meta['user_id'] ?? '') !== (string) $ownerId ||
            ($meta['status'] ?? null) !== 'connected' ||
            ($meta['provisioning_status'] ?? null) !== 'ready') {
            throw new RuntimeException('Server identity, ownership or provisioning readiness invalid.');
        }
    }
}
