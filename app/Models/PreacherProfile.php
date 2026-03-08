<?php

namespace App\Models;

use App\Enums\MinistryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PreacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ministry_name',
        'ministry_type',
        'avatar_url',
        'country_name',
        'country_code',
        'city',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'boolean',
        'social_links' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this preacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all sermons for this preacher.
     */
    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }

    /**
     * Get all sermon views for sermons from this preacher.
     */
    public function sermonViews(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(SermonView::class, Sermon::class);
    }

    /**
     * Get the ministry type description.
     */
    public function getMinistryTypeDescriptionAttribute(): string
    {
        return MinistryType::getDescription($this->ministry_type);
    }

    /**
     * Get full location string (city, country).
     */
    public function getFullLocationAttribute(): ?string
    {
        $parts = array_filter([$this->city, $this->country_name]);
        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Scope to filter by ministry type.
     */
    public function scopeByMinistryType($query, string $type)
    {
        return $query->where('ministry_type', $type);
    }

    /**
     * Scope to filter by country.
     */
    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope to filter only active preacher profiles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the storage folder name for this preacher.
     * Uses slugified ministry_name.
     */
    public function getStorageFolder(): string
    {
        return Str::slug($this->ministry_name ?: ('preacher-' . $this->id));
    }
}
