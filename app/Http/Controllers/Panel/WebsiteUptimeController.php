<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Jobs\CheckWebsiteUptimeJob;
use App\Models\Website;
use App\Models\WebsiteUptimeMonitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WebsiteUptimeController extends Controller
{
    private function site(Request $request, int $website): Website
    {
        return Website::where('user_id', $request->user()->id)->findOrFail($website);
    }

    public function select(Request $request): View
    {
        $websites = Website::where('user_id', $request->user()->id)
            ->orderBy('domain')->get();
        $monitors = WebsiteUptimeMonitor::whereIn('website_id', $websites->pluck('id'))
            ->get()->keyBy('website_id');
        return view('panel.uptime.select', compact('websites', 'monitors'));
    }

    public function index(Request $request, int $website): View
    {
        $site = $this->site($request, $website);
        $monitor = WebsiteUptimeMonitor::where('website_id', $site->id)->first();
        $checks = $monitor ? $monitor->checks()->latest('checked_at')->limit(50)->get() : collect();
        $incidents = $monitor ? $monitor->incidents()->latest('started_at')->limit(20)->get() : collect();
        $lastDay = $monitor ? $monitor->checks()->where('checked_at', '>=', now()->subDay()) : null;
        $total = $lastDay ? (clone $lastDay)->count() : 0;
        $successful = $lastDay ? (clone $lastDay)->where('successful', true)->count() : 0;
        $uptimePercent = $total ? round($successful / $total * 100, 2) : null;
        return view('panel.uptime.index', compact('site', 'monitor', 'checks', 'incidents', 'uptimePercent', 'total'));
    }

    public function enable(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $data = $request->validate([
            'interval_minutes' => ['required', 'integer', 'in:5,10,15,30,60'],
            'expected_status' => ['required', 'integer', 'in:200,204,301,302'],
        ]);
        WebsiteUptimeMonitor::updateOrCreate(['website_id' => $site->id], [
            'enabled' => true,
            'interval_minutes' => $data['interval_minutes'],
            'expected_status' => $data['expected_status'],
            'next_check_at' => now(),
        ]);
        return back()->with('success', 'Uptime monitoring enabled. A check will be queued by the scheduler.');
    }

    public function update(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $monitor = WebsiteUptimeMonitor::where('website_id', $site->id)->firstOrFail();
        $data = $request->validate([
            'interval_minutes' => ['required', 'integer', 'in:5,10,15,30,60'],
            'expected_status' => ['required', 'integer', 'in:200,204,301,302'],
            'enabled' => ['required', 'boolean'],
        ]);
        $monitor->update([
            'interval_minutes' => $data['interval_minutes'],
            'expected_status' => $data['expected_status'],
            'enabled' => $data['enabled'],
            'next_check_at' => now(),
        ]);
        return back()->with('success', 'Monitoring preferences saved.');
    }

    public function check(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        $monitor = WebsiteUptimeMonitor::where('website_id', $site->id)->where('enabled', true)->firstOrFail();
        // Throttle at DB level too, not only via route middleware.
        $claimed = WebsiteUptimeMonitor::whereKey($monitor->id)
            ->where('enabled', true)
            ->where(fn ($q) => $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
            ->update(['next_check_at' => now()->addMinutes(2)]);
        if (!$claimed) return back()->with('success', 'A recent or scheduled check is already pending.');
        CheckWebsiteUptimeJob::dispatch($monitor->id);
        return back()->with('success', 'Uptime check queued.');
    }

    public function disable(Request $request, int $website): RedirectResponse
    {
        $site = $this->site($request, $website);
        WebsiteUptimeMonitor::where('website_id', $site->id)->update(['enabled' => false]);
        return back()->with('success', 'Monitoring paused; history was retained.');
    }
}
