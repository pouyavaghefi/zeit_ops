<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsiteUptimeJob;
use App\Models\WebsiteUptimeMonitor;
use Illuminate\Console\Command;

class ScheduleUptimeChecks extends Command
{
    protected $signature = 'zeitops:uptime-dispatch';
    protected $description = 'Queue due uptime checks without overlapping reservations.';

    public function handle(): int
    {
        $count = 0;
        // Throttle dispatch volume; due checks that cannot be claimed remain queued for the next minute.
        WebsiteUptimeMonitor::where('enabled', true)
            ->where(fn ($q) => $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
            ->orderBy('id')->limit(100)->pluck('id')->each(function ($id) use (&$count) {
                $claimed = WebsiteUptimeMonitor::whereKey($id)->where('enabled', true)
                    ->where(fn ($q) => $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
                    ->update(['next_check_at' => now()->addMinutes(2)]);
                if ($claimed) {
                    CheckWebsiteUptimeJob::dispatch((int) $id);
                    $count++;
                }
            });
        $this->line("Dispatched {$count} uptime checks.");
        return self::SUCCESS;
    }
}
