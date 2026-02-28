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
        'preacher_profile_id',
        'category_sermon_id',
        'title',
        'preacher_name',
        'audio_url',
        'description',
        'is_published',
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
        'preacher_profile_id' => 'integer',
        'is_published' => 'boolean',
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
        return $this->belongsTo(Church::class)->where('is_active', true);
    }

    /**
     * Get the preacher profile that owns this sermon.
     */
    public function preacherProfile(): BelongsTo
    {
        return $this->belongsTo(PreacherProfile::class)->where('is_active', true);
    }

    /**
     * Get the publisher (church or preacher profile) of this sermon.
     * Returns the church if church_id is set, otherwise returns preacher profile.
     */
    public function getPublisher()
    {
        if ($this->church_id) {
            return $this->church;
        }
        return $this->preacherProfile;
    }

    /**
     * Get the publisher name (church name or ministry name).
     */
    public function getPublisherNameAttribute(): string
    {
        if ($this->church_id && $this->church) {
            return $this->church->name;
        }
        if ($this->preacher_profile_id && $this->preacherProfile) {
            return $this->preacherProfile->ministry_name;
        }
        return 'Unknown';
    }

    /**
     * Get the publisher logo/avatar URL.
     */
    public function getPublisherLogoAttribute(): ?string
    {
        if ($this->church_id && $this->church) {
            return $this->church->logo_url;
        }
        if ($this->preacher_profile_id && $this->preacherProfile) {
            return $this->preacherProfile->avatar_url;
        }
        return null;
    }

    /**
     * Check if sermon is published by a church.
     */
    public function isPublishedByChurch(): bool
    {
        return !is_null($this->church_id);
    }

    /**
     * Check if sermon is published by an independent preacher.
     */
    public function isPublishedByPreacher(): bool
    {
        return !is_null($this->preacher_profile_id);
    }

    /**
     * Get the category that owns this sermon.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategorySermon::class, 'category_sermon_id');
    }

    /**
     * Get the category sermon relationship (alias for category).
     */
    public function categorySermon(): BelongsTo
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
     * Get favorites for this sermon (alias for favoritedBy).
     */
    public function favorites()
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

    /**
     * Scope to get only published sermons
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to get only draft sermons
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }
}
