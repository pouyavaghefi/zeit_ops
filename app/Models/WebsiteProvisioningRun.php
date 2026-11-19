<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteProvisioningRun extends Model
{
    protected $fillable = [
        'website_id',
        'server_id',
        'status',
        'percentage',
        'current_operation',
        'message',
        'tasks',
        'logs',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'tasks' => 'array',
        'logs' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
