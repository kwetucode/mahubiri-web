<?php

namespace App\Livewire\Admin\Monitoring;

use App\Models\User;
use App\Models\Sermon;
use App\Models\SermonView;
use App\Models\Church;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RealtimeDashboard extends Component
{
    public $refreshInterval = 30; // Refresh every 30 seconds

    public function mount() {}

    public function render()
    {
        // Activité en temps réel (dernières 24h)
        $realtimeStats = [
            'active_users_today' => $this->getActiveUsersToday(),
            'new_users_today' => $this->getNewUsersToday(),
            'sermons_uploaded_today' => $this->getSermonsUploadedToday(),
            'total_plays_today' => $this->getTotalPlaysToday(),
            'active_churches_today' => $this->getActiveChurchesToday(),
        ];

        // Activité récente (dernière heure)
        $recentActivity = [
            'recent_registrations' => $this->getRecentRegistrations(),
            'recent_uploads' => $this->getRecentUploads(),
            'recent_plays' => $this->getRecentPlays(),
            'trending_sermons' => $this->getTrendingSermons(),
        ];

        // Métriques par heure (dernières 24h)
        $hourlyMetrics = [
            'plays_by_hour' => $this->getPlaysByHour(),
            'uploads_by_hour' => $this->getUploadsByHour(),
            'registrations_by_hour' => $this->getRegistrationsByHour(),
        ];

        // Statistiques globales
        $globalStats = [
            'total_users' => User::count(),
            'total_churches' => Church::count(),
            'total_sermons' => Sermon::count(),
            'total_plays' => SermonView::count(),
        ];

        // Pics d'activité
        $peakActivity = [
            'peak_hour_today' => $this->getPeakHourToday(),
            'most_active_church' => $this->getMostActiveChurch(),
            'most_listened_sermon' => $this->getMostListenedSermon(),
        ];

        return view('livewire.admin.monitoring.realtime-dashboard', [
            'realtimeStats' => $realtimeStats,
            'recentActivity' => $recentActivity,
            'hourlyMetrics' => $hourlyMetrics,
            'globalStats' => $globalStats,
            'peakActivity' => $peakActivity,
        ]);
    }

    /**
     * Get active users today (users who performed any action)
     */
    private function getActiveUsersToday(): int
    {
        return Cache::remember('active_users_today', 300, function () {
            $today = Carbon::today();

            // Users who listened to sermons today
            $listeners = SermonView::whereDate('created_at', $today)
                ->distinct('user_id')
                ->count('user_id');

            // Users who uploaded sermons today
            $uploaders = Sermon::whereDate('created_at', $today)
                ->whereHas('church', function ($q) {
                    $q->whereNotNull('created_by');
                })
                ->distinct('church_id')
                ->count();

            return $listeners + $uploaders;
        });
    }

    /**
     * Get new users registered today
     */
    private function getNewUsersToday(): int
    {
        return User::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * Get sermons uploaded today
     */
    private function getSermonsUploadedToday(): int
    {
        return Sermon::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * Get total plays today
     */
    private function getTotalPlaysToday(): int
    {
        return SermonView::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * Get active churches today (churches that uploaded or have sermons played)
     */
    private function getActiveChurchesToday(): int
    {
        return Cache::remember('active_churches_today', 300, function () {
            $today = Carbon::today();

            $uploadingChurches = Sermon::whereDate('created_at', $today)
                ->distinct('church_id')
                ->pluck('church_id');

            $playedChurches = SermonView::whereDate('sermon_views.created_at', $today)
                ->join('sermons', 'sermon_views.sermon_id', '=', 'sermons.id')
                ->distinct('sermons.church_id')
                ->pluck('sermons.church_id');

            return $uploadingChurches->merge($playedChurches)->unique()->count();
        });
    }

    /**
     * Get recent registrations (last 10)
     */
    private function getRecentRegistrations()
    {
        return User::with('role')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? 'N/A',
                    'created_at' => $user->created_at,
                    'time_ago' => $user->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get recent sermon uploads (last 10)
     */
    private function getRecentUploads()
    {
        return Sermon::with(['church', 'category'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'church_name' => $sermon->church->name ?? 'N/A',
                    'category' => $sermon->category->name ?? 'N/A',
                    'size_mb' => $sermon->size ? round($sermon->size / 1048576, 2) : 0,
                    'is_published' => $sermon->is_published,
                    'created_at' => $sermon->created_at,
                    'time_ago' => $sermon->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get recent plays (last 20)
     */
    private function getRecentPlays()
    {
        return SermonView::with(['sermon', 'user'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($view) {
                return [
                    'sermon_title' => $view->sermon->title ?? 'N/A',
                    'user_name' => $view->user->name ?? 'Anonymous',
                    'created_at' => $view->created_at,
                    'time_ago' => $view->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get trending sermons (most played in last 24h)
     */
    private function getTrendingSermons()
    {
        return Cache::remember('trending_sermons', 300, function () {
            return SermonView::select('sermon_id', DB::raw('COUNT(*) as play_count'))
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->groupBy('sermon_id')
                ->orderBy('play_count', 'desc')
                ->take(5)
                ->with(['sermon.church'])
                ->get()
                ->map(function ($view) {
                    return [
                        'sermon_id' => $view->sermon_id,
                        'title' => $view->sermon->title ?? 'N/A',
                        'church' => $view->sermon->church->name ?? 'N/A',
                        'play_count' => $view->play_count,
                    ];
                });
        });
    }

    /**
     * Get plays by hour (last 24h)
     */
    private function getPlaysByHour()
    {
        return SermonView::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => $item->hour . 'h',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get uploads by hour (last 24h)
     */
    private function getUploadsByHour()
    {
        return Sermon::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => $item->hour . 'h',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get registrations by hour (last 24h)
     */
    private function getRegistrationsByHour()
    {
        return User::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => $item->hour . 'h',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get peak hour today
     */
    private function getPeakHourToday()
    {
        $peakPlay = SermonView::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', Carbon::today())
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        return $peakPlay ? [
            'hour' => $peakPlay->hour . 'h',
            'count' => $peakPlay->count,
        ] : null;
    }

    /**
     * Get most active church today
     */
    private function getMostActiveChurch()
    {
        $church = SermonView::select('sermons.church_id', DB::raw('COUNT(*) as play_count'))
            ->join('sermons', 'sermon_views.sermon_id', '=', 'sermons.id')
            ->whereDate('sermon_views.created_at', Carbon::today())
            ->groupBy('sermons.church_id')
            ->orderBy('play_count', 'desc')
            ->with(['church'])
            ->first();

        return $church ? [
            'name' => optional($church->church)->name ?? 'N/A',
            'play_count' => $church->play_count,
        ] : null;
    }

    /**
     * Get most listened sermon today
     */
    private function getMostListenedSermon()
    {
        $sermon = SermonView::select('sermon_id', DB::raw('COUNT(*) as play_count'))
            ->whereDate('created_at', Carbon::today())
            ->groupBy('sermon_id')
            ->orderBy('play_count', 'desc')
            ->with(['sermon'])
            ->first();

        return $sermon ? [
            'title' => $sermon->sermon->title ?? 'N/A',
            'play_count' => $sermon->play_count,
        ] : null;
    }
}
