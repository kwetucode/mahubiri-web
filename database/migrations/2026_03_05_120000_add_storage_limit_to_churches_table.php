<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->unsignedBigInteger('storage_limit')
                ->default(3 * 1024 * 1024 * 1024) // 3 GB in bytes
                ->after('is_featured')
                ->comment('Storage quota in bytes (default: 3 GB)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('storage_limit');
        });
    }
};
