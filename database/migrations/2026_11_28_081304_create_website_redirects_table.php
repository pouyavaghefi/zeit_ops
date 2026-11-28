<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_dns_zone_id')->constrained('website_dns_zones')->restrictOnDelete();
            $table->string('source_host', 253);
            $table->string('source_path', 512);
            $table->string('destination_url', 2048);
            $table->unsignedSmallInteger('http_status')->default(302);
            $table->boolean('preserve_query_string')->default(true);
            $table->string('cloudflare_ruleset_id', 64);
            $table->string('cloudflare_rule_id', 64);
            $table->timestamps();
            $table->unique(['website_id', 'source_host', 'source_path'], 'website_redirect_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_redirects');
    }
};
