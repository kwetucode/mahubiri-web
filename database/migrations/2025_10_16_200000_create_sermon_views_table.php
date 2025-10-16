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
        Schema::create('sermon_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable()->comment('IP address for anonymous tracking');
            $table->text('user_agent')->nullable()->comment('Browser/device user agent');
            $table->integer('duration_played')->nullable()->comment('Duration played in seconds');
            $table->boolean('completed')->default(false)->comment('Whether the sermon was played to completion');
            $table->timestamp('played_at')->useCurrent()->comment('When the sermon was played');
            $table->timestamps();

            // Index for performance
            $table->index(['sermon_id', 'played_at']);
            $table->index(['user_id', 'played_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sermon_views');
    }
};
