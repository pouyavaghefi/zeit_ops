<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteUptimeIncident extends Model
{
    protected $fillable = ['website_uptime_monitor_id','started_at','resolved_at','reason'];
    protected $casts = ['started_at' => 'datetime', 'resolved_at' => 'datetime'];
    public function monitor() { return $this->belongsTo(WebsiteUptimeMonitor::class, 'website_uptime_monitor_id'); }
}
