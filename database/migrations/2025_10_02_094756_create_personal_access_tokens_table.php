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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->morphs('tokenable');
            $table->text('name')->comment('Token name');
            $table->string('token', 64)->unique()->comment('Token string');
            $table->text('abilities')->nullable()->comment('Token abilities');
            $table->timestamp('last_used_at')->nullable()->comment('Last used at timestamp');
            $table->timestamp('expires_at')->nullable()->index()->comment('Expires at timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
