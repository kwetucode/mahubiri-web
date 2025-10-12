<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sermon extends Model
{
    /** @use HasFactory<\Database\Factories\SermonFactory> */
    use HasFactory;

    protected $fillable = [
        'church_id',
        'category_sermon_id',
        'title',
        'preacher_name',
        'audio_url',
        'description',
        'cover_url',
        'duration',
        'mime_type',
        'size',
        'audio_bitrate',
        'duration_formatted',
        'audio_format',
        'color',
    ];

    protected $casts = [
        'duration' => 'integer',
        'church_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the church that owns this sermon.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }
    /**
     * Get the category that owns this sermon.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategorySermon::class, 'category_sermon_id');
    }
}
