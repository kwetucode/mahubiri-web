<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes for the most common sermon query patterns:
     * - Listing published sermons ordered by created_at
     * - Filtering published sermons by category
     * - Filtering published sermons by church
     */
    public function up(): void
    {
        Schema::table('sermons', function (Blueprint $table) {
            $table->index(['is_published', 'created_at'], 'idx_sermons_published_created');
            $table->index(['is_published', 'category_sermon_id', 'created_at'], 'idx_sermons_published_category_created');
            $table->index(['is_published', 'church_id', 'created_at'], 'idx_sermons_published_church_created');
            $table->index(['is_published', 'popularity_score'], 'idx_sermons_published_popularity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sermons', function (Blueprint $table) {
            $table->dropIndex('idx_sermons_published_created');
            $table->dropIndex('idx_sermons_published_category_created');
            $table->dropIndex('idx_sermons_published_church_created');
            $table->dropIndex('idx_sermons_published_popularity');
        });
    }
};
