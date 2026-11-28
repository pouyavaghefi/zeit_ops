<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WebsiteDnsZone extends Model
{
    protected $fillable = ['website_id', 'provider', 'zone_name', 'provider_zone_id', 'api_token'];

    protected $hidden = ['api_token'];

    protected function casts(): array
    {
        return ['api_token' => 'encrypted'];
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
