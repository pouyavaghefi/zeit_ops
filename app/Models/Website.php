<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $fillable = [
        'user_id',
        'server_id',
        'name',
        'domain',
        'stack',
        'framework',
        'php_version',
        'repository_url',
        'repository_branch',
        'document_root',
        'status',
        'last_deployed_at',
    ];

    protected $casts = [
        'last_deployed_at' => 'datetime',
    ];

    public function dnsZones()
    {
        return $this->hasMany(WebsiteDnsZone::class);
    }

    public function domains()
    {
        return $this->hasMany(WebsiteDomain::class);
    }

    public function deployments()
    {
        return $this->hasMany(WebsiteDeployment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
