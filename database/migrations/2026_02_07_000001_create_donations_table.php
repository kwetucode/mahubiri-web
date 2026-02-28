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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('preacher_profile_id')->nullable()->constrained()->onDelete('set null');

            // Transaction details
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('CDF');
            $table->string('country_code', 5)->default('DRC');
            $table->string('phone_number', 20);

            // Shwary transaction info
            $table->string('shwary_transaction_id')->nullable()->index();
            $table->string('shwary_reference_id')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('failure_reason')->nullable();
            $table->boolean('is_sandbox')->default(false);

            // Optional message from donor
            $table->text('message')->nullable();

            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['church_id', 'status']);
            $table->index(['preacher_profile_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
