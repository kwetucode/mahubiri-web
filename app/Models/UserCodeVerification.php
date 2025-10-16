<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserCodeVerification extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'code',
        'type',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the user that owns the code verification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a random 6-digit code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification code for a user
     */
    public static function createForUser(User $user, string $type = 'password_reset', int $expiresInMinutes = 15): self
    {
        // Supprimer les anciens codes non utilisés pour ce type
        self::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        return self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => self::generateCode(),
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Verify if the code is valid
     */
    public function isValid(): bool
    {
        return !$this->is_used
            && $this->expires_at->isFuture();
    }

    /**
     * Mark the code as used
     */
    public function markAsUsed(): bool
    {
        $this->is_used = true;
        $this->used_at = Carbon::now();
        return $this->save();
    }

    /**
     * Mark the code as used and delete it from database
     */
    public function markAsUsedAndDelete(): bool
    {
        $this->is_used = true;
        $this->used_at = Carbon::now();
        $this->save();

        // Supprimer le code de la base de données
        return $this->delete();
    }

    /**
     * Scope to get only valid codes
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to get codes by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Find and verify a code for a user
     */
    public static function verifyCode(string $email, string $code, string $type = 'password_reset'): ?self
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->valid()
            ->latest()
            ->first();

        return $verification;
    }
}
