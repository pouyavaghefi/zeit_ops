<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('queued');
            $table->string('stage', 60)->default('queued');
            $table->boolean('run_migrations')->default(false);
            $table->string('commit_sha', 40)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['website_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_deployments');
    }
};
