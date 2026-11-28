<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteUptimeCheck extends Model
{
    public $timestamps = false;
    protected $fillable = ['website_uptime_monitor_id','successful','http_status','response_ms','reason','checked_at'];
    protected $casts = ['successful' => 'boolean', 'checked_at' => 'datetime'];
    public function monitor() { return $this->belongsTo(WebsiteUptimeMonitor::class, 'website_uptime_monitor_id'); }
}
