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
        'popularity_score',
        'popularity_calculated_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'church_id' => 'integer',
        'popularity_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'popularity_calculated_at' => 'datetime',
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

    /**
     * Get users who favorited this sermon
     */
    public function favoritedBy()
    {
        return $this->hasMany(SermonFavorite::class);
    }

    /**
     * Get views/plays for this sermon
     */
    public function views()
    {
        return $this->hasMany(SermonView::class);
    }

    /**
     * Check if sermon is favorited by a specific user
     */
    public function isFavoritedBy($userId): bool
    {
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Scope to order by popularity score
     */
    public function scopePopular($query)
    {
        return $query->orderBy('popularity_score', 'desc');
    }

    /**
     * Scope to get sermons with minimum popularity score
     */
    public function scopeMinimumPopularity($query, $minScore = 0)
    {
        return $query->where('popularity_score', '>', $minScore);
    }
}
