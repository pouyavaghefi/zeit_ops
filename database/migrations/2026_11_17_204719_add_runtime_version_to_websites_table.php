<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('runtime_version', 20)->nullable();
            $table->string('php_version', 10)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('runtime_version');
            $table->string('php_version', 10)
                ->default('8.3')
                ->nullable(false)
                ->change();
        });
    }
};
