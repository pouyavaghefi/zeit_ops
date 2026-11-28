<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_uptime_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->unsignedSmallInteger('interval_minutes')->default(5);
            $table->unsignedSmallInteger('expected_status')->default(200);
            $table->string('status', 16)->default('unknown');
            $table->unsignedSmallInteger('consecutive_failures')->default(0);
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->unsignedInteger('last_response_ms')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->timestamps();
            $table->index(['enabled', 'next_check_at'], 'uptime_monitors_due_idx');
        });

        Schema::create('website_uptime_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_uptime_monitor_id')
                ->constrained('website_uptime_monitors')->cascadeOnDelete();
            $table->boolean('successful');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_ms')->nullable();
            $table->string('reason', 120)->nullable();
            $table->timestamp('checked_at');
            $table->index(['website_uptime_monitor_id', 'checked_at'], 'uptime_checks_monitor_at_idx');
        });

        Schema::create('website_uptime_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_uptime_monitor_id')
                ->constrained('website_uptime_monitors')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('reason', 120)->nullable();
            $table->timestamps();
            $table->index(['website_uptime_monitor_id', 'resolved_at'], 'uptime_incidents_resolved_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_uptime_incidents');
        Schema::dropIfExists('website_uptime_checks');
        Schema::dropIfExists('website_uptime_monitors');
    }
};
