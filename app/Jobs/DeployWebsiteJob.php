<?php

namespace App\Jobs;

use App\Models\Website;
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
 * Phase 1: validate remote Git access and inspect the requested ref.
 *
 * This is intentionally NOT a website-publishing operation. It does not
 * execute repository contents, create a DB, modify Apache, or mark a site
 * active. A per-site execution identity and restricted deployment helper
 * must exist before arbitrary customer code can safely be run on a VPS.
 */
final class DeployWebsiteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 180;
    public bool $failOnTimeout = true;

    public function __construct(public int $websiteId)
    {
        $this->onQueue('deployments');
    }

    public function middleware(): array
    {
        $website = Website::findOrFail($this->websiteId);

        return [
            (new WithoutOverlapping('zeitops-server-' . $website->server_id))
                ->shared()
                ->releaseAfter(30)
                ->expireAfter(240),
        ];
    }

    public function handle(ServerSshService $servers): void
    {
        $website = Website::findOrFail($this->websiteId);
        $serverId = (string) $website->server_id;

        try {
            if ((string) $website->stack !== 'lamp' ||
                (string) $website->framework !== 'laravel') {
                throw new RuntimeException('Automatic repository checks currently support Laravel/LAMP only.');
            }

            $url = $this->normalizeGithubUrl((string) $website->repository_url);
            $branch = $this->validateBranch((string) ($website->repository_branch ?: 'main'));
            $this->assertServerReady($serverId, (int) $website->user_id);

            Log::channel('provisioning')->info('Repository preflight started', [
                'website_id' => $website->id,
                'server_id' => $serverId,
                'branch' => $branch,
            ]);

            $ssh = $servers->connect($serverId, (int) $website->user_id);
            $identity = $servers->execute($ssh, 'id -un', 30);
            if ($identity !== 'zeitops') {
                throw new RuntimeException('Unexpected SSH management account.');
            }

            // Do not interpolate unescaped customer input into the shell.
            // Disable interactive credential prompts so private repos fail cleanly.
            $command = 'GIT_TERMINAL_PROMPT=0 GIT_ASKPASS=/bin/false ' .
                'git -c credential.helper= ls-remote --exit-code --heads ' .
                escapeshellarg($url) . ' ' . escapeshellarg('refs/heads/' . $branch);

            $output = trim($servers->execute($ssh, $command, 90));
            if (!preg_match('/^([0-9a-f]{40})\s+refs\/heads\/' .
                preg_quote($branch, '/') . '$/D', $output, $match)) {
                throw new RuntimeException('Git did not return an expected commit for the configured branch.');
            }

            Log::channel('provisioning')->info('Repository accessible; publication not yet configured', [
                'website_id' => $website->id,
                'server_id' => $serverId,
                'branch' => $branch,
                'commit' => $match[1],
                'next_step' => 'Install site-isolated deployment executor and restricted Apache helper',
            ]);

            // Never set active or last_deployed_at for an accessibility check.
        } catch (Throwable $e) {
            Log::channel('provisioning')->error('Repository preflight failed', [
                'website_id' => $website->id,
                'server_id' => $serverId,
                'exception_class' => get_class($e),
                // Fixed messages only: do not leak Git credentials or raw remote output.
                'reason' => $e instanceof RuntimeException ? $e->getMessage() : 'Unexpected repository preflight error',
            ]);
            throw $e;
        }
    }

    private function normalizeGithubUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('~^https://github\.com/([A-Za-z0-9-]{1,39})/([A-Za-z0-9._-]{1,100})(?:\.git)?/?$~D', $url, $m)) {
            throw new RuntimeException('Only credential-free github.com HTTPS repository URLs are supported.');
        }
        // Avoid ambiguous path components and suffix tricks.
        $repo = preg_replace('/\.git$/', '', $m[2]);
        if ($repo === '' || $repo === '.' || $repo === '..' || str_starts_with($repo, '-')) {
            throw new RuntimeException('Invalid GitHub repository name.');
        }
        return 'https://github.com/' . $m[1] . '/' . $repo . '.git';
    }

    private function validateBranch(string $branch): string
    {
        if (strlen($branch) > 150 ||
            !preg_match('~^[A-Za-z0-9][A-Za-z0-9._/-]*$~D', $branch) ||
            str_contains($branch, '..') || str_contains($branch, '//') ||
            str_contains($branch, '@{') || str_ends_with($branch, '.') ||
            str_ends_with($branch, '/') || str_ends_with($branch, '.lock')) {
            throw new RuntimeException('Invalid Git branch name.');
        }
        return $branch;
    }

    private function assertServerReady(string $serverId, int $ownerId): void
    {
        if (!Str::isUuid($serverId)) {
            throw new RuntimeException('Invalid registered server identifier.');
        }
        $path = storage_path("app/private/zeitops/servers/{$serverId}/server.json");
        if (!File::isFile($path)) {
            throw new RuntimeException('Server metadata is missing.');
        }
        $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || ($data['id'] ?? null) !== $serverId ||
            (string) ($data['user_id'] ?? '') !== (string) $ownerId ||
            ($data['status'] ?? null) !== 'connected' ||
            ($data['provisioning_status'] ?? null) !== 'ready') {
            throw new RuntimeException('Server ownership, SSH connection, or bootstrap status is invalid.');
        }
    }
}
