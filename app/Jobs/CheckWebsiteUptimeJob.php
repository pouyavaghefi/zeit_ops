<?php

namespace App\Jobs;

use App\Models\WebsiteUptimeMonitor;
use App\Services\Monitoring\HttpsProbe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CheckWebsiteUptimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 20;

    public function __construct(public int $monitorId) { $this->onQueue('monitoring'); }

    public function handle(HttpsProbe $probe): void
    {
        $monitor = WebsiteUptimeMonitor::with('website')->find($this->monitorId);
        if (!$monitor || !$monitor->enabled || !$monitor->website) return;

        [$success, $http, $duration, $reason] = $probe->check($monitor->website, $monitor->expected_status);
        DB::transaction(function () use ($success, $http, $duration, $reason) {
            $monitor = WebsiteUptimeMonitor::whereKey($this->monitorId)->lockForUpdate()->first();
            if (!$monitor || !$monitor->enabled) return;
            $time = now();
            $monitor->checks()->create([
                'successful' => $success, 'http_status' => $http,
                'response_ms' => $duration, 'reason' => $reason, 'checked_at' => $time,
            ]);
            $monitor->last_checked_at = $time;
            $monitor->last_http_status = $http;
            $monitor->last_response_ms = $duration;
            $monitor->next_check_at = $time->copy()->addMinutes($monitor->interval_minutes);
            if ($success) {
                if ($monitor->status === 'down') {
                    $monitor->incidents()->whereNull('resolved_at')->update(['resolved_at' => $time]);
                }
                $monitor->consecutive_failures = 0;
                $monitor->status = 'up';
            } else {
                $monitor->consecutive_failures++;
                if ($monitor->consecutive_failures >= 2 && $monitor->status !== 'down') {
                    $monitor->incidents()->create(['started_at' => $time, 'reason' => $reason]);
                    $monitor->status = 'down';
                }
            }
            $monitor->save();
        });
    }
}
