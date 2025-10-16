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
        Schema::table('sermons', function (Blueprint $table) {
            $table->decimal('popularity_score', 10, 2)->default(0)->after('description')->comment('Calculated popularity score');
            $table->timestamp('popularity_calculated_at')->nullable()->after('popularity_score')->comment('Last time popularity was calculated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sermons', function (Blueprint $table) {
            $table->dropColumn(['popularity_score', 'popularity_calculated_at']);
        });
    }
};
