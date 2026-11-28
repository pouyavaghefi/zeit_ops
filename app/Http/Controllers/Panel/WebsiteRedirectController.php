<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\WebsiteDnsZone;
use App\Models\WebsiteDomain;
use App\Models\WebsiteRedirect;
use App\Services\Dns\CloudflareRedirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

final class WebsiteRedirectController extends Controller
{
    public function __construct(private CloudflareRedirects $provider) {}

    private function site(Request $request, int $website): Website
    {
        return Website::where('user_id', $request->user()->id)->findOrFail($website);
    }

    public function select(Request $request): View
    {
        $websites = Website::where('user_id', $request->user()->id)->orderBy('domain')->get();
        return view('panel.redirects.select', compact('websites'));
    }

    public function index(Request $request, int $website): View
    {
        $site = $this->site($request, $website);
        $zones = WebsiteDnsZone::where('website_id', $site->id)->orderBy('zone_name')->get();
        $aliases = WebsiteDomain::where('website_id', $site->id)
            ->where('status', 'active')->orderBy('domain')->pluck('domain');
        $hosts = collect([$site->domain])->merge($aliases)->unique()->values();
        $redirects = WebsiteRedirect::where('website_id', $site->id)->with('zone')->latest()->get();
        return view('panel.redirects.index', compact('site', 'zones', 'hosts', 'redirects'));
    }

    public function store(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $data = $request->validate([
            'website_dns_zone_id' => ['required','integer'],
            'source_host' => ['required','string','max:253'],
            'source_path' => ['required','string','max:512'],
            'destination_url' => ['required','url:http,https','max:2048'],
            'http_status' => ['required','integer','in:301,302,307,308'],
            'preserve_query_string' => ['nullable','boolean'],
        ]);
        $zone = WebsiteDnsZone::where('website_id', $site->id)->findOrFail($data['website_dns_zone_id']);
        $host = strtolower(trim($data['source_host']));
        $path = $data['source_path'];
        // Restrict input to exact URL paths; no wildcard expressions or arbitrary CF DSL.
        if (!preg_match('~^/(?:[A-Za-z0-9._~%/-]*)$~D', $path) || str_contains($path, '//') || str_contains($path, '..')) {
            throw ValidationException::withMessages(['source_path' => 'Use an exact path beginning with /. No wildcards, query strings, or parent-directory segments.']);
        }
        $allowedHosts = [strtolower($site->domain)];
        $allowedHosts = array_merge($allowedHosts, WebsiteDomain::where('website_id', $site->id)
            ->where('status','active')->pluck('domain')->map(fn ($value) => strtolower($value))->all());
        if (!in_array($host, $allowedHosts, true)) {
            throw ValidationException::withMessages(['source_host' => 'Choose the primary domain or an active verified subdomain.']);
        }
        $zoneName = strtolower($zone->zone_name);
        if ($host !== $zoneName && !str_ends_with($host, '.'.$zoneName)) {
            throw ValidationException::withMessages(['website_dns_zone_id' => 'The Cloudflare zone must be authoritative for this hostname.']);
        }
        // No unintentional self-loop and no unsupported redirect schemes/userinfo.
        $targetParts = parse_url($data['destination_url']);
        if (!is_array($targetParts) || !in_array(strtolower($targetParts['scheme'] ?? ''), ['https','http'], true)
            || isset($targetParts['user']) || isset($targetParts['pass']) || isset($targetParts['fragment'])) {
            throw ValidationException::withMessages(['destination_url' => 'Use a normal http(s) URL without credentials or fragments.']);
        }
        $targetHost = strtolower($targetParts['host'] ?? '');
        $targetPath = $targetParts['path'] ?? '/';
        if ($targetHost === $host && $targetPath === $path && empty($targetParts['query'])) {
            throw ValidationException::withMessages(['destination_url' => 'The redirect would loop to the same URL.']);
        }
        if (WebsiteRedirect::where('website_id', $site->id)->where('source_host', $host)->where('source_path', $path)->exists()) {
            throw ValidationException::withMessages(['source_path' => 'An existing redirect already uses this hostname and path.']);
        }
        try {
            [$rulesetId, $ruleId] = $this->provider->create(
                $zone->api_token, $zone->provider_zone_id, $host, $path,
                $data['destination_url'], (int) $data['http_status'],
                $request->boolean('preserve_query_string'), (int) $site->id
            );
            try {
                WebsiteRedirect::create([
                    'website_id' => $site->id, 'website_dns_zone_id' => $zone->id,
                    'source_host' => $host, 'source_path' => $path,
                    'destination_url' => $data['destination_url'],
                    'http_status' => (int) $data['http_status'],
                    'preserve_query_string' => $request->boolean('preserve_query_string'),
                    'cloudflare_ruleset_id' => $rulesetId, 'cloudflare_rule_id' => $ruleId,
                ]);
            } catch (Throwable $exception) {
                // Best-effort compensation when Cloudflare succeeded but DB failed.
                try { $this->provider->delete($zone->api_token, $zone->provider_zone_id, $rulesetId, $ruleId); }
                catch (Throwable $ignored) { report($ignored); }
                throw $exception;
            }
            return back()->with('success', 'Cloudflare redirect created. Verify it from an external browser.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['redirect' => $exception->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, int $website, int $redirect): RedirectResponse
    {
        $site = $this->site($request, $website);
        $entry = WebsiteRedirect::where('website_id', $site->id)->with('zone')->findOrFail($redirect);
        if (!$entry->zone || (int) $entry->zone->website_id !== (int) $site->id) {
            abort(409, 'Connected DNS zone is missing; reconcile records manually.');
        }
        try {
            $this->provider->delete($entry->zone->api_token, $entry->zone->provider_zone_id,
                $entry->cloudflare_ruleset_id, $entry->cloudflare_rule_id);
            $entry->delete();
            return back()->with('success', 'Cloudflare redirect removed.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['redirect' => $exception->getMessage()]);
        }
    }
}
