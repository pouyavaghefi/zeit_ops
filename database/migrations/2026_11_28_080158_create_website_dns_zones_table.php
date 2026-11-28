<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_dns_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->default('cloudflare');
            $table->string('zone_name', 253);
            $table->string('provider_zone_id', 64);
            $table->text('api_token'); // Encrypted using Laravel's encrypted cast.
            $table->timestamps();
            $table->unique(['website_id', 'zone_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_dns_zones');
    }
};
