<?php

use App\Models\CategorySermon;
use App\Models\Church;
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
        Schema::create('sermons', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->foreignIdFor(Church::class)->constrained('churches')->onDelete('cascade')->comment('Foreign key to churches table');
            $table->foreignIdFor(CategorySermon::class)->constrained('category_sermons')->onDelete('set null')->nullable()->comment('Foreign key to category_sermons table');
            $table->string('title')->comment('Title of the sermon');
            $table->string('preacher_name')->comment('Name of the preacher');
            $table->text('audio_url')->comment('URL of the audio file');
            $table->text('cover_url')->nullable()->comment('URL of the cover image');
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->string('mime_type')->nullable()->comment('MIME type of the audio file');
            $table->bigInteger('size')->nullable()->comment('Size of the audio file');
            $table->integer('audio_bitrate')->nullable()->comment('Audio bitrate');
            $table->string('duration_formatted')->nullable()->comment('Formatted duration');
            $table->string('audio_format')->nullable()->comment('Audio format');
            $table->string('color')->nullable()->comment('Color');
            $table->text('description')->nullable()->comment('Description of the sermon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};
