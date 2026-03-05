<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'new_sermon',
        'new_church',
        'new_announcement',
        'storage_alert',
        'push_enabled',
        'email_enabled',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'new_sermon' => 'boolean',
        'new_church' => 'boolean',
        'new_announcement' => 'boolean',
        'storage_alert' => 'boolean',
        'push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this notification settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default notification settings
     */
    public static function getDefaults(): array
    {
        return [
            'new_sermon' => true,
            'new_church' => true,
            'new_announcement' => true,
            'storage_alert' => true,
            'push_enabled' => true,
            'email_enabled' => false,
        ];
    }

    /**
     * Check if a specific notification type is enabled
     */
    public function isNotificationEnabled(string $type): bool
    {
        if (!$this->push_enabled) {
            return false;
        }

        return match ($type) {
            'new_sermon' => $this->new_sermon,
            'new_church' => $this->new_church,
            'new_announcement' => $this->new_announcement,
            'storage_alert' => $this->storage_alert,
            default => false,
        };
    }
}
