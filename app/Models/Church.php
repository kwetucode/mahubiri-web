<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends Model
{
    /** @use HasFactory<\Database\Factories\ChurchFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'visionary_name',
        'logo_url',
        'description',
        'country_name',
        'country_code',
        'city',
        'address',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this church.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all sermons for this church.
     */
    public function sermons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sermon::class);
    }

    /**
     * Get all sermon views for sermons from this church.
     */
    public function sermonViews(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(SermonView::class, Sermon::class);
    }
}
