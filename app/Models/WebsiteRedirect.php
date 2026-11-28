<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WebsiteRedirect extends Model
{
    protected $fillable = [
        'website_id', 'website_dns_zone_id', 'source_host', 'source_path',
        'destination_url', 'http_status', 'preserve_query_string',
        'cloudflare_ruleset_id', 'cloudflare_rule_id',
    ];

    protected function casts(): array
    {
        return ['preserve_query_string' => 'boolean', 'http_status' => 'integer'];
    }

    public function zone()
    {
        return $this->belongsTo(WebsiteDnsZone::class, 'website_dns_zone_id');
    }
}
