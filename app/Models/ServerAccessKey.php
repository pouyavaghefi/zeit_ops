<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerAccessKey extends Model
{
    protected $fillable = [
        'user_id', 'server_id', 'label', 'fingerprint', 'public_key',
        'status', 'last_error', 'installed_at', 'revoked_at',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
