<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SermonView extends Model
{
    use HasFactory;

    protected $fillable = [
        'sermon_id',
        'user_id',
        'ip_address',
        'user_agent',
        'duration_played',
        'completed',
        'played_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'duration_played' => 'integer',
        'played_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sermon that this view belongs to
     */
    public function sermon(): BelongsTo
    {
        return $this->belongsTo(Sermon::class);
    }

    /**
     * Get the user who viewed this sermon (can be null for anonymous views)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for completed views only
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope for views within a time period
     */
    public function scopeWithinPeriod($query, $days = 30)
    {
        return $query->where('played_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for views by authenticated users only
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }
}
