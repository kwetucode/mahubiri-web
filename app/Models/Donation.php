<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'church_id',
        'preacher_profile_id',
        'amount',
        'currency',
        'country_code',
        'phone_number',
        'shwary_transaction_id',
        'shwary_reference_id',
        'status',
        'failure_reason',
        'is_sandbox',
        'message',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_sandbox' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($donation) {
            if (empty($donation->uuid)) {
                $donation->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who made the donation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the church receiving the donation.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the preacher receiving the donation.
     */
    public function preacherProfile(): BelongsTo
    {
        return $this->belongsTo(PreacherProfile::class);
    }

    /**
     * Scope for pending donations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed donations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed donations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if donation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if donation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if donation is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark donation as completed.
     */
    public function markAsCompleted(string $shwaryTransactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'shwary_transaction_id' => $shwaryTransactionId ?? $this->shwary_transaction_id,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark donation as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Get formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get the recipient name (church or preacher).
     */
    public function getRecipientNameAttribute(): string
    {
        if ($this->church) {
            return $this->church->name;
        }

        if ($this->preacherProfile) {
            return $this->preacherProfile->name ?? $this->preacherProfile->user->name ?? 'Prédicateur';
        }

        return 'Inconnu';
    }
}
