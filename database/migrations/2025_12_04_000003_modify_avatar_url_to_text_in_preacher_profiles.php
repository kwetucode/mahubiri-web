<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the column to TEXT
        DB::statement('ALTER TABLE preacher_profiles MODIFY COLUMN avatar_url TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to VARCHAR(255)
        DB::statement('ALTER TABLE preacher_profiles MODIFY COLUMN avatar_url VARCHAR(255) NULL');
    }
};
