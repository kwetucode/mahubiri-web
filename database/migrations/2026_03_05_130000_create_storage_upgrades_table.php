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
        Schema::create('storage_upgrades', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('UUID public pour les références');
            $table->foreignId('church_id')->constrained('churches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('extra_bytes')->comment('Stockage supplémentaire acheté en octets');
            $table->decimal('amount', 12, 2)->comment('Montant payé');
            $table->string('currency', 10)->default('CDF');
            $table->string('country_code', 10)->default('DRC');
            $table->string('phone_number', 20);
            $table->string('shwary_transaction_id')->nullable();
            $table->string('shwary_reference_id')->nullable();
            $table->string('status')->default('pending')->comment('pending, completed, failed');
            $table->string('failure_reason')->nullable();
            $table->boolean('is_sandbox')->default(false);
            $table->boolean('is_applied')->default(false)->comment('Si le quota a été ajouté à l\'église');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
            $table->index('shwary_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_upgrades');
    }
};
