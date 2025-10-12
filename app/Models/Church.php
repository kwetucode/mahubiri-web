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
        'logo_url',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_by' => 'integer',
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
}
