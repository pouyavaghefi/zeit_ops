<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            $table->enum('billing_period', [
                'monthly',
                'yearly',
                'lifetime'
            ])->default('monthly');

            $table->unsignedInteger('max_servers')->default(1);
            $table->unsignedInteger('max_websites')->default(1);
            $table->unsignedInteger('max_deployments_per_month')
                ->default(10);

            $table->boolean('monitoring_enabled')->default(false);
            $table->boolean('automated_backups_enabled')->default(false);
            $table->boolean('custom_domains_enabled')->default(true);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
