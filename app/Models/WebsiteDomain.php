<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteDomain extends Model
{
    protected $fillable = [
        'website_id', 'domain', 'status', 'operation', 'verification_token',
        'verified_at', 'certificate_expires_at', 'last_error',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'certificate_expires_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
