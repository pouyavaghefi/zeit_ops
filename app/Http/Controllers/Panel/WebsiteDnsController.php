<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\WebsiteDnsZone;
use App\Services\Dns\CloudflareDns;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

final class WebsiteDnsController extends Controller
{
    public function __construct(private CloudflareDns $dns) {}

    private function site(Request $request, int $website): Website
    {
        return Website::where('user_id', $request->user()->id)->findOrFail($website);
    }

    private function zone(Website $site, int $zone): WebsiteDnsZone
    {
        return WebsiteDnsZone::where('website_id', $site->id)->findOrFail($zone);
    }

    public function select(Request $request): View
    {
        return view('panel.dns.select', [
            'websites' => Website::where('user_id', $request->user()->id)->orderBy('domain')->get(),
        ]);
    }

    public function index(Request $request, int $website): View
    {
        $site = $this->site($request, $website);
        $zones = WebsiteDnsZone::where('website_id', $site->id)->orderBy('zone_name')->get();
        $chosen = $request->integer('zone');
        $selected = $zones->firstWhere('id', $chosen) ?: $zones->first();
        $page = max(1, min(10000, $request->integer('page', 1)));
        $records = [];
        $totalPages = 1;
        $problem = null;
        if ($selected) {
            try {
                $data = $this->dns->records($selected->api_token, $selected->provider_zone_id, $page);
                $records = $data['items'];
                $totalPages = $data['pages'];
            } catch (\Throwable $e) {
                report($e);
                $problem = 'Could not load live DNS records. Check the Cloudflare token and zone configuration.';
            }
        }
        return view('panel.dns.index', compact('site', 'zones', 'selected', 'records', 'totalPages', 'page', 'problem'));
    }

    public function connect(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $data = $request->validate([
            'zone_name' => ['required','string','max:253','regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
            'zone_id' => ['required','regex:/^[a-f0-9]{32}$/i'],
            'api_token' => ['required','string','min:10','max:512'],
        ]);
        $name = strtolower($data['zone_name']);
        $owned = [strtolower($site->domain)];
        // A pending alias may be managed so its DNS can be configured BEFORE SSL verification.
        foreach ($site->domains()->pluck('domain') as $domain) $owned[] = strtolower($domain);
        $inScope = false;
        foreach ($owned as $domain) {
            if ($domain === $name || str_ends_with($domain, '.'.$name)) { $inScope = true; break; }
        }
        if (!$inScope) return back()->withErrors(['zone_name' => 'This zone must cover the selected website or a registered domain alias.']);
        try {
            $providerZone = $this->dns->inspectZone($data['api_token'], strtolower($data['zone_id']));
            if (strtolower((string) ($providerZone['name'] ?? '')) !== $name) {
                return back()->withErrors(['zone_name' => 'Cloudflare returned a different DNS zone name.']);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['api_token' => 'Cannot verify this Cloudflare zone with the supplied token. Requires Zone Read and DNS Edit permissions.']);
        }
        WebsiteDnsZone::updateOrCreate(
            ['website_id' => $site->id, 'zone_name' => $name],
            ['provider' => 'cloudflare', 'provider_zone_id' => strtolower($data['zone_id']), 'api_token' => $data['api_token']]
        );
        return redirect()->route('panel.websites.dns.index', $site->id)->with('success', 'Cloudflare DNS zone connected. Records shown below are live provider records.');
    }

    public function disconnect(Request $request, int $website, int $zone): RedirectResponse
    {
        $site = $this->site($request, $website);
        $this->zone($site, $zone)->delete();
        return redirect()->route('panel.websites.dns.index', $site->id)->with('success', 'DNS provider disconnected. No public DNS records were changed.');
    }

    private function validatedRecord(Request $request, WebsiteDnsZone $zone): array
    {
        $data = $request->validate([
            'type' => ['required','in:A,AAAA,CNAME,TXT,MX'],
            'name' => ['required','string','max:253'],
            'content' => ['required','string','max:2048'],
            'ttl' => ['required','integer','in:1,60,120,300,600,1800,3600,7200,14400,28800,43200,86400'],
            'priority' => ['nullable','integer','between:0,65535'],
            'proxied' => ['nullable','boolean'],
        ]);
        $name = strtolower(trim($data['name']));
        if ($name === '@') $name = $zone->zone_name;
        elseif ($name !== $zone->zone_name && !str_ends_with($name, '.'.$zone->zone_name)) {
            // Relative names are permitted, but cannot escape the configured zone.
            $name .= '.'.$zone->zone_name;
        }
        if (!preg_match('/^(?:[a-z0-9_](?:[a-z0-9_-]{0,61}[a-z0-9_])?\.)+[a-z]{2,63}$/iD', $name)) {
            throw ValidationException::withMessages(['name' => 'Invalid DNS name.']);
        }
        $type = $data['type'];
        $content = trim($data['content']);
        if ($type === 'A' && !filter_var($content, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw ValidationException::withMessages(['content' => 'Enter a valid IPv4 address.']);
        }
        if ($type === 'AAAA' && !filter_var($content, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw ValidationException::withMessages(['content' => 'Enter a valid IPv6 address.']);
        }
        if (in_array($type, ['CNAME', 'MX'], true)) {
            $content = strtolower(rtrim($content, '.'));
            if (!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/iD', $content)) {
                throw ValidationException::withMessages(['content' => 'Enter a valid fully qualified hostname.']);
            }
        }
        if ($type === 'TXT' && (strlen($content) > 1024 || $content === '')) {
            throw ValidationException::withMessages(['content' => 'TXT content must be between 1 and 1024 bytes.']);
        }
        $record = ['type' => $type, 'name' => $name, 'content' => $content, 'ttl' => (int) $data['ttl']];
        if ($type === 'MX') $record['priority'] = (int) ($data['priority'] ?? 10);
        if (in_array($type, ['A','AAAA','CNAME'], true)) $record['proxied'] = $request->boolean('proxied');
        return $record;
    }

    public function store(Request $request, int $website, int $zone): RedirectResponse
    {
        $site = $this->site($request, $website);
        $connected = $this->zone($site, $zone);
        $data = $this->validatedRecord($request, $connected);
        try { $this->dns->create($connected->api_token, $connected->provider_zone_id, $data); }
        catch (\Throwable $e) { return back()->withErrors(['record' => 'Cloudflare rejected the DNS change. Check for conflicts, permissions, and record values.']); }
        return back()->with('success', 'DNS record created at Cloudflare. Propagation and caching may take time.');
    }

    public function update(Request $request, int $website, int $zone, string $record): RedirectResponse
    {
        $site = $this->site($request, $website);
        $connected = $this->zone($site, $zone);
        abort_unless(preg_match('/^[a-f0-9]{32}$/iD', $record), 404);
        $data = $this->validatedRecord($request, $connected);
        try {
            $existing = $this->dns->record($connected->api_token, $connected->provider_zone_id, $record);
            if (strtoupper((string) ($existing['type'] ?? '')) !== $data['type']) {
                return back()->withErrors(['record' => 'Cannot change a record type in place. Delete and recreate it instead.']);
            }
            $this->dns->update($connected->api_token, $connected->provider_zone_id, $record, $data);
        } catch (\Throwable $e) { return back()->withErrors(['record' => 'Could not update this DNS record. Verify that it still exists and is editable.']); }
        return back()->with('success', 'DNS record updated at Cloudflare.');
    }

    public function destroy(Request $request, int $website, int $zone, string $record): RedirectResponse
    {
        $site = $this->site($request, $website);
        $connected = $this->zone($site, $zone);
        abort_unless(preg_match('/^[a-f0-9]{32}$/iD', $record), 404);
        try { $this->dns->delete($connected->api_token, $connected->provider_zone_id, $record); }
        catch (\Throwable $e) { return back()->withErrors(['record' => 'DNS record removal failed. It may have been changed or deleted at the provider.']); }
        return back()->with('success', 'DNS record deleted at Cloudflare.');
    }
}
