<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('server_access_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Existing ZeitOps servers are stored in private per-server JSON, not a SQL table.
            $table->uuid('server_id');
            $table->string('label', 100);
            $table->string('fingerprint', 80);
            $table->text('public_key'); // public keys only; NEVER private keys
            $table->string('status', 20)->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'server_id'], 'server_access_owner_server_idx');
            $table->index(['server_id', 'fingerprint'], 'server_access_fingerprint_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_access_keys');
    }
};
