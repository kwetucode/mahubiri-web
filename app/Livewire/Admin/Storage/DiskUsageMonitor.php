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
                    'name' => $church->name,
                    'sermon_count' => $church->sermons_count,
                    'used_bytes' => $usedBytes,
                    'used_mb' => round($usedBytes / (1024 * 1024), 2),
                    'used_gb' => round($usedBytes / (1024 * 1024 * 1024), 2),
                    'quota_gb' => 3.0,
                    'remaining_gb' => round((($quotaBytes - $usedBytes) / (1024 * 1024 * 1024)), 2),
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
                        return $church['name'];
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
     * Calculate saturation forecast
     */
    private function getSaturationForecast()
    {
        // Calculate average daily upload
        $last30Days = Sermon::where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('size');
        
        $avgDailyUpload = $last30Days / 30;

        $totalQuotaBytes = 3 * 1024 * 1024 * 1024 * Church::count();
        $currentUsage = Sermon::sum('size') ?? 0;
        $remaining = $totalQuotaBytes - $currentUsage;

        $daysUntilFull = $avgDailyUpload > 0 
            ? ceil($remaining / $avgDailyUpload) 
            : 999;

        return [
            'avg_daily_upload_mb' => round($avgDailyUpload / (1024 * 1024), 2),
            'remaining_space_gb' => round($remaining / (1024 * 1024 * 1024), 2),
            'days_until_full' => $daysUntilFull > 365 ? '> 1 an' : $daysUntilFull . ' jours',
            'estimated_date' => $daysUntilFull < 365 
                ? Carbon::now()->addDays($daysUntilFull)->format('d/m/Y') 
                : 'N/A',
        ];
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
