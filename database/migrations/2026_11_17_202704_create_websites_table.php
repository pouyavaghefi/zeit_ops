
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // UUID from the existing server.json metadata.
            $table->uuid('server_id');

            $table->string('name', 120);
            $table->string('domain', 253);

            // Current preset: Laravel + LAMP.
            $table->string('stack', 40)->default('lamp');
            $table->string('framework', 40)->default('laravel');

            $table->string('php_version', 10)->default('8.3');

            $table->string('repository_url', 500)->nullable();
            $table->string('repository_branch', 120)
                ->default('main');

            $table->string('document_root', 255)
                ->default('public');

            $table->string('status', 40)
                ->default('pending_provisioning');

            $table->timestamp('last_deployed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['server_id', 'domain'],
                'websites_server_domain_unique'
            );

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
