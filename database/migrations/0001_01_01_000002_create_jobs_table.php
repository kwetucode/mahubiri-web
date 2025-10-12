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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('queue')->index()->comment('Queue name');
            $table->longText('payload')->comment('Job payload');
            $table->unsignedTinyInteger('attempts')->comment('Number of attempts');
            $table->unsignedInteger('reserved_at')->nullable()->comment('Reserved at timestamp');
            $table->unsignedInteger('available_at')->comment('Available at timestamp');
            $table->unsignedInteger('created_at')->comment('Created at timestamp');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Primary key');
            $table->string('name')->comment('Batch name');
            $table->integer('total_jobs')->comment('Total jobs in the batch');
            $table->integer('pending_jobs')->comment('Pending jobs count');
            $table->integer('failed_jobs')->comment('Failed jobs count');
            $table->longText('failed_job_ids')->comment('Failed job IDs');
            $table->mediumText('options')->nullable()->comment('Batch options');
            $table->integer('cancelled_at')->nullable()->comment('Cancelled at timestamp');
            $table->integer('created_at')->comment('Created at timestamp');
            $table->integer('finished_at')->nullable()->comment('Finished at timestamp');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->comment('Unique identifier');
            $table->text('connection')->comment('Connection name');
            $table->text('queue')->comment('Queue name');
            $table->longText('payload')->comment('Job payload');
            $table->longText('exception')->comment('Exception details');
            $table->timestamp('failed_at')->useCurrent()->comment('Failed at timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
