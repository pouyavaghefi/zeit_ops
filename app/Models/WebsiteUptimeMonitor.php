<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteUptimeMonitor extends Model
{
    protected $fillable = ['website_id', 'enabled', 'interval_minutes', 'expected_status', 'status', 'consecutive_failures', 'last_http_status', 'last_response_ms', 'last_checked_at', 'next_check_at'];
    protected $casts = ['enabled' => 'boolean', 'last_checked_at' => 'datetime', 'next_check_at' => 'datetime'];

    public function website() { return $this->belongsTo(Website::class); }
    public function checks() { return $this->hasMany(WebsiteUptimeCheck::class); }
    public function incidents() { return $this->hasMany(WebsiteUptimeIncident::class); }
}
