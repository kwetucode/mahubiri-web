<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends Model
{
    /** @use HasFactory<\Database\Factories\ChurchFactory> */
    use HasFactory;

    /**
     * Default storage limit in bytes (3 GB).
     */
    public const DEFAULT_STORAGE_LIMIT = 3 * 1024 * 1024 * 1024;

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
        'is_featured',
        'storage_limit',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'storage_limit' => 'integer',
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

    /**
     * Scope to filter only active churches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total storage used by sermons (in bytes).
     */
    public function getStorageUsedBytes(): int
    {
        return (int) $this->sermons()->sum('size');
    }

    /**
     * Get the storage limit in bytes.
     */
    public function getStorageLimitBytes(): int
    {
        return $this->storage_limit ?? self::DEFAULT_STORAGE_LIMIT;
    }

    /**
     * Get remaining storage in bytes.
     */
    public function getStorageRemainingBytes(): int
    {
        return max(0, $this->getStorageLimitBytes() - $this->getStorageUsedBytes());
    }

    /**
     * Get storage usage percentage.
     */
    public function getStorageUsedPercentage(): float
    {
        $limit = $this->getStorageLimitBytes();
        if ($limit <= 0) {
            return 100.0;
        }

        return round(($this->getStorageUsedBytes() / $limit) * 100, 2);
    }

    /**
     * Check if quota is exceeded (no more space available).
     */
    public function isStorageQuotaExceeded(): bool
    {
        return $this->getStorageUsedBytes() >= $this->getStorageLimitBytes();
    }

    /**
     * Check if there is enough space for a given file size.
     */
    public function hasEnoughStorage(int $fileSizeBytes): bool
    {
        return ($this->getStorageUsedBytes() + $fileSizeBytes) <= $this->getStorageLimitBytes();
    }

    /**
     * Get the storage status: normal, warning, critical.
     */
    public function getStorageStatus(): string
    {
        $percentage = $this->getStorageUsedPercentage();

        if ($percentage >= 90) {
            return 'critical';
        }

        if ($percentage >= 75) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Get full storage quota summary.
     */
    public function getStorageQuotaSummary(): array
    {
        $limitBytes = $this->getStorageLimitBytes();
        $usedBytes = $this->getStorageUsedBytes();
        $remainingBytes = max(0, $limitBytes - $usedBytes);
        $usedPercentage = $this->getStorageUsedPercentage();
        $status = $this->getStorageStatus();

        return [
            'storage_limit_bytes' => $limitBytes,
            'storage_limit_gb' => round($limitBytes / (1024 * 1024 * 1024), 2),
            'storage_used_bytes' => $usedBytes,
            'storage_used_gb' => round($usedBytes / (1024 * 1024 * 1024), 2),
            'storage_remaining_bytes' => $remainingBytes,
            'storage_remaining_gb' => round($remainingBytes / (1024 * 1024 * 1024), 2),
            'storage_used_percentage' => $usedPercentage,
            'storage_status' => $status,
            'can_upload' => !$this->isStorageQuotaExceeded(),
            'upgrade_required' => $this->isStorageQuotaExceeded(),
            'upgrade_message' => $this->isStorageQuotaExceeded()
                ? 'Votre quota de stockage est épuisé. Veuillez mettre à jour votre abonnement pour continuer à publier des sermons.'
                : null,
        ];
    }
}
