<?php

namespace App\Jobs;

use App\Models\WebsiteDomain;
use App\Services\Provisioning\ServerSshService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class ManageWebsiteDomainJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 20;
    public int $timeout = 600;
    public bool $failOnTimeout = true;

    public function __construct(public int $domainId, public string $action)
    {
        $this->onQueue('deployments');
    }

    public function middleware(): array
    {
        $domain = WebsiteDomain::with('website')->findOrFail($this->domainId);
        return [(new WithoutOverlapping('zeitops-server-' . $domain->website->server_id))
            ->shared()->releaseAfter(45)->expireAfter(3700)];
    }

    public function handle(ServerSshService $servers): void
    {
        $entry = WebsiteDomain::with('website')->findOrFail($this->domainId);
        $site = $entry->website;
        if (!in_array($this->action, ['issue', 'remove'], true) || $entry->operation !== $this->action) {
            return;
        }
        try {
            $ssh = $servers->connect((string) $site->server_id, (int) $site->user_id);
            if ($servers->execute($ssh, 'id -un', 30) !== 'zeitops') {
                throw new RuntimeException('Remote management identity invalid.');
            }
            $arguments = [$this->action, (string) $site->id, $entry->domain, $entry->verification_token];
            $command = 'sudo -n /usr/local/libexec/zeitops/domains ' . implode(' ', array_map('escapeshellarg', $arguments));
            $output = $servers->execute($ssh, $command, 480);
            if ($this->action === 'remove') {
                if (!str_contains($output, 'ZEITOPS_DOMAIN_REMOVED')) throw new RuntimeException('Domain removal was not confirmed.');
                $entry->delete();
                return;
            }
            if (!preg_match('/ZEITOPS_DOMAIN_HTTPS_READY\s+expires=(\d{4}-\d{2}-\d{2})/', $output, $matches)) {
                throw new RuntimeException('HTTPS verification was not confirmed by the VPS.');
            }
            $entry->update([
                'status' => 'active', 'operation' => null,
                'certificate_expires_at' => $matches[1] . ' 00:00:00', 'last_error' => null,
            ]);
        } catch (Throwable $e) {
            $entry->update([
                'status' => 'failed', 'operation' => null,
                'last_error' => 'Remote SSL operation failed. Check the VPS domain log.',
            ]);
            Log::channel('provisioning')->error('Domain operation failed', [
                'domain_id' => $entry->id, 'action' => $this->action,
                'error_class' => get_class($e),
            ]);
            throw $e;
        }
    }

    public function failed(?Throwable $e): void
    {
        WebsiteDomain::whereKey($this->domainId)->whereNotNull('operation')->update([
            'status' => 'failed', 'operation' => null,
            'last_error' => 'The domain worker failed or timed out. Check server logs.',
        ]);
    }
}
