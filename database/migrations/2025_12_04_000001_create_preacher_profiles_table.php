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
        Schema::create('preacher_profiles', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade')->comment('Foreign key to users table');
            $table->string('ministry_name')->comment('Name of the ministry');
            $table->enum('ministry_type', ['pasteur', 'apotre', 'evangeliste', 'prophete', 'enseignant', 'docteur'])->comment('Type of ministry');
            $table->text('avatar_url')->nullable()->comment('URL of the avatar image');
            $table->string('country_name')->nullable()->comment('Country name');
            $table->string('country_code', 10)->nullable()->comment('ISO country code');
            $table->string('city')->nullable()->comment('City name');
            $table->json('social_links')->nullable()->comment('Social media links (Facebook, YouTube, etc.)');
            $table->boolean('is_verified')->default(false)->comment('Whether the preacher is verified');
            $table->boolean('is_active')->default(true)->comment('Whether the profile is active');
            $table->timestamps();

            // Indexes
            $table->index('ministry_type');
            $table->index('is_verified');
            $table->index('is_active');
            $table->index(['country_code', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preacher_profiles');
    }
};
