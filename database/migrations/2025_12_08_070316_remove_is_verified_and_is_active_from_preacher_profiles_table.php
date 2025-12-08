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
        Schema::table('preacher_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preacher_profiles', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('social_links');
            $table->boolean('is_active')->default(true)->after('is_verified');
        });
    }
};
