<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StorageUpgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'church_id',
        'user_id',
        'extra_bytes',
        'amount',
        'currency',
        'country_code',
        'phone_number',
        'shwary_transaction_id',
        'shwary_reference_id',
        'status',
        'failure_reason',
        'is_sandbox',
        'is_applied',
        'completed_at',
    ];

    protected $casts = [
        'extra_bytes' => 'integer',
        'amount' => 'decimal:2',
        'is_sandbox' => 'boolean',
        'is_applied' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Storage upgrade plans (extra bytes and price in CDF).
     */
    public const PLANS = [
        '1gb' => [
            'label' => '1 Go supplémentaire',
            'extra_bytes' => 1 * 1024 * 1024 * 1024,
            'prices' => [
                'CDF' => 5800,
                'KES' => 200,
                'UGX' => 7000,
            ],
        ],
        '3gb' => [
            'label' => '3 Go supplémentaires',
            'extra_bytes' => 3 * 1024 * 1024 * 1024,
            'prices' => [
                'CDF' => 14500,
                'KES' => 500,
                'UGX' => 17500,
            ],
        ],
        '5gb' => [
            'label' => '5 Go supplémentaires',
            'extra_bytes' => 5 * 1024 * 1024 * 1024,
            'prices' => [
                'CDF' => 23200,
                'KES' => 800,
                'UGX' => 28000,
            ],
        ],
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($upgrade) {
            if (empty($upgrade->uuid)) {
                $upgrade->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the church that purchased this upgrade.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the user who initiated the upgrade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the upgrade as completed and apply storage to the church.
     */
    public function markAsCompleted(?string $transactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'shwary_transaction_id' => $transactionId ?? $this->shwary_transaction_id,
            'completed_at' => now(),
        ]);

        // Apply the extra storage to the church
        if (!$this->is_applied) {
            $church = $this->church;
            $church->increment('storage_limit', $this->extra_bytes);
            $this->update(['is_applied' => true]);
        }
    }

    /**
     * Mark the upgrade as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Get a plan by key.
     */
    public static function getPlan(string $planKey): ?array
    {
        return self::PLANS[$planKey] ?? null;
    }

    /**
     * Get all available plans.
     */
    public static function getAvailablePlans(): array
    {
        return self::PLANS;
    }
}
