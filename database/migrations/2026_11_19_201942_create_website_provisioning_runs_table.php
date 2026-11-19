
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_provisioning_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('website_id')
                ->constrained('websites')
                ->cascadeOnDelete();

            $table->uuid('server_id');

            $table->string('status', 30)->default('queued');
            $table->unsignedTinyInteger('percentage')->default(0);

            $table->string('current_operation')->nullable();
            $table->text('message')->nullable();

            $table->json('tasks');
            $table->json('logs')->nullable();

            $table->text('error')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['website_id', 'id']);
            $table->index(['server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_provisioning_runs');
    }
};
