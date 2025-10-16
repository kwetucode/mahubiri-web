<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SermonFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sermon_id',
    ];

    /**
     * Get the user that owns the favorite
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the sermon that is favorited
     */
    public function sermon(): BelongsTo
    {
        return $this->belongsTo(Sermon::class, 'sermon_id');
    }
}
