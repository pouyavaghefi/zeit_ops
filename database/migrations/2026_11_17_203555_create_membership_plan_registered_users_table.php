<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plan_registered_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('membership_plan_id')
                ->constrained('membership_plans')
                ->restrictOnDelete();

            // 0 = inactive, 1 = active, 2 = expired, 3 = cancelled
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('auto_renew')->default(false);

            $table->string('payment_provider', 50)->nullable();
            $table->string('provider_subscription_id', 255)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');

            $table->unique(
                ['payment_provider', 'provider_subscription_id'],
                'membership_provider_subscription_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_registered_users');
    }
};
