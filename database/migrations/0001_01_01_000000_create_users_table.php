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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('name')->comment('Name of the user');
            $table->string('email')->unique()->comment('Email address of the user');
            $table->string('phone', 20)->nullable()->comment('Phone number of the user');
            $table->timestamp('email_verified_at')->nullable()->comment('Email verification timestamp');
            $table->string('password')->comment('Password for the user');
            $table->rememberToken()->comment('Remember me token');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Email address of the user');
            $table->string('token')->comment('Password reset token');
            $table->timestamp('created_at')->nullable()->comment('Token creation timestamp');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Session ID');
            $table->foreignId('user_id')->nullable()->index()->comment('Foreign key to users table');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->longText('payload')->comment('Session payload');
            $table->integer('last_activity')->index()->comment('Last activity timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
