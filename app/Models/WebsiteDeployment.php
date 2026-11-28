<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteDeployment extends Model
{
    protected $fillable = [
        'website_id', 'status', 'stage', 'run_migrations',
        'commit_sha', 'error', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'run_migrations' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
