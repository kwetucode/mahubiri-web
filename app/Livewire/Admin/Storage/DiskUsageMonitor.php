<?php

namespace App\Livewire\Admin\Storage;

use App\Models\Church;
use App\Models\Sermon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiskUsageMonitor extends Component
{
    public $sortBy = 'usage'; // usage, church_name, sermon_count
    public $sortDirection = 'desc';
    public $filterStatus = 'all'; // all, warning, critical, normal

    public function mount() {}

    public function render()
    {
        $quotaBytes = 3 * 1024 * 1024 * 1024; // 3 GB

        // Vue d'ensemble globale
        $globalStorage = [
            'total_quota_gb' => 3 * Church::count(),
            'total_used_gb' => round(Sermon::sum('size') / (1024 * 1024 * 1024), 2),
            'total_sermons' => Sermon::count(),
            'total_churches' => Church::count(),
            'avg_size_mb' => Sermon::count() > 0 
                ? round((Sermon::sum('size') / Sermon::count()) / (1024 * 1024), 2) 
                : 0,
        ];

        $globalStorage['usage_percentage'] = $globalStorage['total_quota_gb'] > 0
            ? round(($globalStorage['total_used_gb'] / $globalStorage['total_quota_gb']) * 100, 1)
            : 0;

        // Consommation par église
        $churchStorage = $this->getChurchStorageUsage($quotaBytes);

        // Églises par statut
        $statusDistribution = [
            'normal' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        foreach ($churchStorage as $church) {
            $statusDistribution[$church['status']]++;
        }

        // Tendance de consommation (30 derniers jours)
        $storagetrend = $this->getStorageTrend();

        // Top fichiers volumineux
        $largestSermons = $this->getLargestSermons();

        // Prévision de saturation
        $saturationForecast = $this->getSaturationForecast();

        return view('livewire.admin.storage.disk-usage-monitor', [
            'churchStorageUsage' => $churchStorage,
            'saturationForecast' => $saturationForecast,
            'largestSermons' => $largestSermons,
        ]);
    }

    /**
     * Get storage usage per church
     */
    private function getChurchStorageUsage($quotaBytes)
    {
        $churches = Church::withCount('sermons')
            ->with(['sermons' => function ($q) {
                $q->select('church_id', DB::raw('SUM(size) as total_size'))
                    ->groupBy('church_id');
            }])
            ->get()
            ->map(function ($church) use ($quotaBytes) {
                $usedBytes = $church->sermons->first()->total_size ?? 0;
                $usedPercentage = $usedBytes > 0 
                    ? round(($usedBytes / $quotaBytes) * 100, 2) 
                    : 0;

                $status = 'normal';
                if ($usedPercentage >= 90) {
                    $status = 'critical';
                } elseif ($usedPercentage >= 75) {
                    $status = 'warning';
                }

                return [
                    'id' => $church->id,
                    'church_name' => $church->name,
                    'sermon_count' => $church->sermons_count,
                    'used_bytes' => $usedBytes,
                    'used_mb' => round($usedBytes / (1024 * 1024), 2),
                    'used_gb' => round($usedBytes / (1024 * 1024 * 1024), 2),
                    'quota_bytes' => $quotaBytes,
                    'quota_gb' => 3.0,
                    'remaining_bytes' => $quotaBytes - $usedBytes,
                    'remaining_gb' => round((($quotaBytes - $usedBytes) / (1024 * 1024 * 1024)), 2),
                    'percentage_used' => $usedPercentage,
                    'used_percentage' => $usedPercentage,
                    'remaining_percentage' => round(100 - $usedPercentage, 2),
                    'status' => $status,
                    'created_at' => $church->created_at,
                ];
            })
            ->filter(function ($church) {
                if ($this->filterStatus === 'all') {
                    return true;
                }
                return $church['status'] === $this->filterStatus;
            })
            ->sortBy(function ($church) {
                switch ($this->sortBy) {
                    case 'church_name':
                        return $church['church_name'];
                    case 'sermon_count':
                        return $church['sermon_count'];
                    case 'usage':
                    default:
                        return $church['used_bytes'];
                }
            }, SORT_REGULAR, $this->sortDirection === 'desc')
            ->values()
            ->toArray();

        return $churches;
    }

    /**
     * Get storage trend over last 30 days
     */
    private function getStorageTrend()
    {
        return Sermon::selectRaw('DATE(created_at) as date, SUM(size) as total_size')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d/m'),
                    'size_gb' => round($item->total_size / (1024 * 1024 * 1024), 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get largest sermons
     */
    private function getLargestSermons()
    {
        return Sermon::with(['church'])
            ->orderBy('size', 'desc')
            ->take(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'church' => $sermon->church->name ?? 'N/A',
                    'size_mb' => round($sermon->size / (1024 * 1024), 2),
                    'size_gb' => round($sermon->size / (1024 * 1024 * 1024), 3),
                    'duration' => $sermon->duration_formatted ?? 'N/A',
                    'created_at' => $sermon->created_at->format('d/m/Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Calculate saturation forecast per church
     */
    private function getSaturationForecast()
    {
        $quotaBytes = 3 * 1024 * 1024 * 1024;

        return Church::withCount('sermons')
            ->get()
            ->map(function ($church) use ($quotaBytes) {
                // Get average daily upload for last 30 days
                $last30DaysUpload = Sermon::where('church_id', $church->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->sum('size');
                
                $avgDailyUpload = $last30DaysUpload / 30;

                // Current usage
                $currentUsage = Sermon::where('church_id', $church->id)->sum('size') ?? 0;
                $remaining = $quotaBytes - $currentUsage;

                $daysUntilFull = $avgDailyUpload > 0 
                    ? ceil($remaining / $avgDailyUpload) 
                    : null;

                // Only show churches that will be full in less than 180 days and have activity
                if ($daysUntilFull === null || $daysUntilFull > 180 || $avgDailyUpload == 0) {
                    return null;
                }

                return [
                    'church_name' => $church->name,
                    'avg_daily_upload' => $avgDailyUpload,
                    'days_until_full' => $daysUntilFull,
                    'estimated_date' => Carbon::now()->addDays($daysUntilFull)->format('d/m/Y'),
                ];
            })
            ->filter()
            ->sortBy('days_until_full')
            ->values()
            ->toArray();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatedFilterStatus()
    {
        // Reload data when filter changes
    }
}
