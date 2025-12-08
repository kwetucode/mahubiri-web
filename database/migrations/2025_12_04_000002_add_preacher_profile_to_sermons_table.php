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
        Schema::table('sermons', function (Blueprint $table) {
            // Make church_id nullable
            $table->foreignId('church_id')->nullable()->change();

            // Add preacher_profile_id
            $table->foreignId('preacher_profile_id')->nullable()->after('church_id')
                ->constrained('preacher_profiles')->onDelete('cascade')
                ->comment('Foreign key to preacher_profiles table');

            // Add index
            $table->index('preacher_profile_id');
        });

        // Add check constraint: either church_id or preacher_profile_id must be set
        DB::statement('
            ALTER TABLE sermons
            ADD CONSTRAINT check_sermon_publisher
            CHECK (
                (church_id IS NOT NULL AND preacher_profile_id IS NULL) OR
                (church_id IS NULL AND preacher_profile_id IS NOT NULL)
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint first (MySQL syntax)
        DB::statement('ALTER TABLE sermons DROP CHECK check_sermon_publisher');

        Schema::table('sermons', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['preacher_profile_id']);
            $table->dropColumn('preacher_profile_id');

            // Make church_id required again
            $table->foreignId('church_id')->nullable(false)->change();
        });
    }
};
