<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 253)->unique();
            $table->string('status', 32)->default('pending_dns');
            $table->string('operation', 20)->nullable();
            $table->char('verification_token', 64);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('certificate_expires_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->index(['website_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_domains');
    }
};
