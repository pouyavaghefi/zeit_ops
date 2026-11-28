<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Jobs\ManageWebsiteDomainJob;
use App\Models\Website;
use App\Models\WebsiteDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

final class WebsiteDomainController extends Controller
{
    private function site(Request $request, int $website): Website
    {
        return Website::where('user_id', $request->user()->id)->findOrFail($website);
    }

    private function domain(Website $site, int $domain): WebsiteDomain
    {
        return WebsiteDomain::where('website_id', $site->id)->findOrFail($domain);
    }

    public function index(Request $request, int $website): View
    {
        $site = $this->site($request, $website);
        $domains = WebsiteDomain::where('website_id', $site->id)->latest()->get();
        $latestDeployment = $site->deployments()->latest('id')->first();
        $serverIp = null;
        try {
            $meta = $this->server($site);
            $serverIp = $meta['ip'] ?? $meta['ip_address'] ?? null;
        } catch (RuntimeException $exception) {
            // Domain listing stays accessible when VPS metadata is unavailable.
        }
        return view('panel.websites.domains', compact('site', 'domains', 'latestDeployment', 'serverIp'));
    }

    public function store(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $data = $request->validate(['domain' => ['required', 'string', 'max:253']]);
        $domain = strtolower(rtrim(trim($data['domain']), '.'));
        if (!$this->validDomain($domain) || $domain === strtolower($site->domain)) {
            return back()->withErrors(['domain' => 'Enter a valid additional domain, different from the primary domain.'])->withInput();
        }
        if ($site->stack !== 'lamp' || $site->framework !== 'laravel') {
            return back()->withErrors(['domain' => 'Domain management currently supports Laravel/LAMP websites.']);
        }
        if (Website::where('domain', $domain)->exists() || WebsiteDomain::where('domain', $domain)->exists()) {
            return back()->withErrors(['domain' => 'This domain is already registered in ZeitOps.']);
        }
        WebsiteDomain::create([
            'website_id' => $site->id,
            'domain' => $domain,
            'verification_token' => Str::lower(Str::random(64)),
            'status' => 'pending_dns',
        ]);
        return back()->with('success', 'Domain added. Add its DNS records, then verify ownership.');
    }

    public function verify(Request $request, int $website, int $domain): RedirectResponse
    {
        $site = $this->site($request, $website);
        $entry = $this->domain($site, $domain);
        if ($entry->operation) return back()->withErrors(['domain' => 'An operation is already in progress.']);

        try {
            $server = $this->server($site);
            $ip = (string) ($server['ip'] ?? $server['ip_address'] ?? '');
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new RuntimeException('Registered VPS IPv4 address is missing.');
            }
            $addresses = dns_get_record($entry->domain, DNS_A) ?: [];
            $resolved = array_column($addresses, 'ip');
            if (!in_array($ip, $resolved, true)) {
                throw new RuntimeException('DNS A record must point to the registered VPS IPv4 address.');
            }
            $txt = dns_get_record('_zeitops-verify.' . $entry->domain, DNS_TXT) ?: [];
            $verified = false;
            foreach ($txt as $record) {
                $value = $record['txt'] ?? implode('', $record['entries'] ?? []);
                if (is_string($value) && hash_equals('zeitops-verification=' . $entry->verification_token, $value)) {
                    $verified = true;
                    break;
                }
            }
            if (!$verified) {
                throw new RuntimeException('Verification TXT record is missing or does not match. DNS propagation may take time.');
            }
            $entry->update(['status' => 'verified', 'verified_at' => now(), 'last_error' => null]);
            return back()->with('success', 'DNS ownership and server IP verified. You can now issue SSL.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['domain' => $exception->getMessage()]);
        }
    }

    public function issue(Request $request, int $website, int $domain): RedirectResponse
    {
        $site = $this->site($request, $website);
        $entry = $this->domain($site, $domain);
        if (!$entry->verified_at) return back()->withErrors(['domain' => 'Verify domain DNS ownership before issuing SSL.']);
        if (!in_array($site->status, ['active', 'deployed'], true)) {
            return back()->withErrors(['domain' => 'Deploy your primary website first.']);
        }
        if ($entry->operation) return back()->withErrors(['domain' => 'An operation is already running.']);
        if ($entry->update(['status' => 'issuing', 'operation' => 'issue', 'last_error' => null])) {
            ManageWebsiteDomainJob::dispatch($entry->id, 'issue');
        }
        return back()->with('success', 'SSL issuance queued. Refresh this page for the result.');
    }

    public function destroy(Request $request, int $website, int $domain): RedirectResponse
    {
        $site = $this->site($request, $website);
        $entry = $this->domain($site, $domain);
        if ($entry->operation) return back()->withErrors(['domain' => 'An operation is already running.']);
        if ($entry->status === 'pending_dns' || $entry->status === 'verified') {
            $entry->delete();
            return back()->with('success', 'Unconfigured domain removed.');
        }
        $entry->update(['status' => 'removing', 'operation' => 'remove', 'last_error' => null]);
        ManageWebsiteDomainJob::dispatch($entry->id, 'remove');
        return back()->with('success', 'Domain removal and alias certificate cleanup queued.');
    }

    private function validDomain(string $domain): bool
    {
        return strlen($domain) <= 253 && (bool) preg_match(
                '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D', $domain
            );
    }

    private function server(Website $site): array
    {
        $path = storage_path('app/private/zeitops/servers/' . $site->server_id . '/server.json');
        if (!File::isFile($path)) throw new RuntimeException('VPS metadata not found.');
        $record = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (($record['user_id'] ?? null) != $site->user_id || ($record['id'] ?? null) !== $site->server_id) {
            throw new RuntimeException('VPS ownership verification failed.');
        }
        return $record;
    }
}
