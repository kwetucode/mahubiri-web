<?php

namespace App\Livewire\Admin\Analytics;

use App\Models\User;
use App\Models\Sermon;
use App\Models\SermonView;
use App\Models\Church;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAnalytics extends Component
{
    public $period = '30'; // Default: last 30 days
    public $selectedMetric = 'overview';

    public function mount() {}

    public function render()
    {
        $startDate = Carbon::now()->subDays((int)$this->period);

        // Vue d'ensemble utilisateurs
        $userOverview = [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'active_users' => $this->getActiveUsers($startDate),
            'users_by_role' => $this->getUsersByRole(),
        ];

        // Croissance utilisateurs
        $userGrowth = $this->getUserGrowth($startDate);

        // Rétention
        $retention = $this->getRetentionMetrics();

        // Engagement
        $engagement = $this->getEngagementMetrics($startDate);

        // Segmentation par rôle
        $roleSegmentation = $this->getRoleSegmentation($startDate);

        // Top utilisateurs actifs
        $topUsers = $this->getTopActiveUsers($startDate);

        // Statistiques de conversion
        $conversion = $this->getConversionStats($startDate);

        return view('livewire.admin.analytics.user-analytics', [
            'retentionMetrics' => $retention,
            'engagementMetrics' => $engagement,
            'conversionStats' => $conversion,
            'roleSegmentation' => $roleSegmentation,
            'topUsers' => $topUsers,
        ]);
    }

    /**
     * Get active users (users who listened to at least one sermon)
     */
    private function getActiveUsers($startDate): int
    {
        return SermonView::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Get users by role
     */
    private function getUsersByRole()
    {
        return User::select('role_id', DB::raw('COUNT(*) as count'))
            ->with('role:id,name')
            ->groupBy('role_id')
            ->get()
            ->map(function ($item) {
                return [
                    'role' => $item->role->name ?? 'N/A',
                    'count' => $item->count,
                    'percentage' => User::count() > 0 
                        ? round(($item->count / User::count()) * 100, 1) 
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get user growth over time
     */
    private function getUserGrowth($startDate)
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d/m/Y'),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get retention metrics
     */
    private function getRetentionMetrics()
    {
        // Day 1 retention
        $day1Users = User::where('created_at', '>=', Carbon::now()->subDays(2))
            ->where('created_at', '<', Carbon::now()->subDay())
            ->count();
        
        $day1Active = SermonView::whereHas('user', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(2))
                  ->where('created_at', '<', Carbon::now()->subDay());
            })
            ->whereDate('sermon_views.created_at', Carbon::yesterday())
            ->distinct('user_id')
            ->count('user_id');

        // Day 7 retention
        $day7Users = User::where('created_at', '>=', Carbon::now()->subDays(8))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();
        
        $day7Active = SermonView::whereHas('user', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(8))
                  ->where('created_at', '<', Carbon::now()->subDays(7));
            })
            ->whereDate('sermon_views.created_at', '>=', Carbon::now()->subDay())
            ->distinct('user_id')
            ->count('user_id');

        // Day 30 retention
        $day30Users = User::where('created_at', '>=', Carbon::now()->subDays(31))
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();
        
        $day30Active = SermonView::whereHas('user', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(31))
                  ->where('created_at', '<', Carbon::now()->subDays(30));
            })
            ->whereDate('sermon_views.created_at', '>=', Carbon::now()->subDay())
            ->distinct('user_id')
            ->count('user_id');

        $totalNewUsers = User::where('created_at', '>=', Carbon::now()->subDays(31))
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();

        return [
            'new_users' => $totalNewUsers,
            'returning_d1' => $day1Active,
            'retention_d1' => $day1Users > 0 ? round(($day1Active / $day1Users) * 100, 1) : 0,
            'returning_d7' => $day7Active,
            'retention_d7' => $day7Users > 0 ? round(($day7Active / $day7Users) * 100, 1) : 0,
            'returning_d30' => $day30Active,
            'retention_d30' => $day30Users > 0 ? round(($day30Active / $day30Users) * 100, 1) : 0,
        ];
    }

    /**
     * Get engagement metrics
     */
    private function getEngagementMetrics($startDate)
    {
        $totalUsers = User::count();
        $activeUsers = $this->getActiveUsers($startDate);

        // Average sessions per user
        $avgSessions = $activeUsers > 0
            ? round(SermonView::where('created_at', '>=', $startDate)->count() / $activeUsers, 1)
            : 0;

        // Users with favorites
        $usersWithFavorites = DB::table('sermon_favorites')
            ->distinct('user_id')
            ->count('user_id');

        return [
            'active_users' => $activeUsers,
            'engagement_rate' => $totalUsers > 0 
                ? round(($activeUsers / $totalUsers) * 100, 1) 
                : 0,
            'avg_sessions' => $avgSessions,
            'users_with_favorites' => $usersWithFavorites,
            'favorite_rate' => $totalUsers > 0 
                ? round(($usersWithFavorites / $totalUsers) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get role segmentation with activity
     */
    private function getRoleSegmentation($startDate)
    {
        return User::select('role_id', DB::raw('COUNT(*) as total'))
            ->with('role:id,name')
            ->groupBy('role_id')
            ->get()
            ->map(function ($item) use ($startDate) {
                $activeCount = SermonView::whereHas('user', function ($q) use ($item) {
                    $q->where('role_id', $item->role_id);
                })
                ->where('sermon_views.created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count('user_id');

                $avgPlays = $item->total > 0
                    ? SermonView::whereHas('user', function ($q) use ($item) {
                        $q->where('role_id', $item->role_id);
                    })
                    ->where('sermon_views.created_at', '>=', $startDate)
                    ->count() / $item->total
                    : 0;

                $avgFavorites = $item->total > 0
                    ? DB::table('sermon_favorites')
                        ->join('users', 'sermon_favorites.user_id', '=', 'users.id')
                        ->where('users.role_id', $item->role_id)
                        ->where('sermon_favorites.created_at', '>=', $startDate)
                        ->count() / $item->total
                    : 0;

                return [
                    'role_name' => $item->role->name ?? 'N/A',
                    'total_users' => $item->total,
                    'active_users' => $activeCount,
                    'activity_rate' => $item->total > 0 
                        ? round(($activeCount / $item->total) * 100, 1) 
                        : 0,
                    'avg_plays' => round($avgPlays, 1),
                    'avg_favorites' => round($avgFavorites, 1),
                ];
            })
            ->toArray();
    }

    /**
     * Get top active users
     */
    private function getTopActiveUsers($startDate)
    {
        return User::withCount([
                'sermonViews as views_count' => function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                },
                'favoriteSermons as favorites_count' => function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }
            ])
            ->with('roles')
            ->whereHas('sermonViews', function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->addSelect([
                'last_view_at' => SermonView::select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->where('created_at', '>=', $startDate)
                    ->latest()
                    ->limit(1)
            ])
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get conversion statistics
     */
    private function getConversionStats($startDate)
    {
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        
        // Users who listened within first day
        $firstDayListeners = User::where('users.created_at', '>=', $startDate)
            ->join('sermon_views', function ($join) {
                $join->on('users.id', '=', 'sermon_views.user_id')
                     ->whereRaw('DATE(sermon_views.created_at) = DATE(users.created_at)');
            })
            ->distinct('users.id')
            ->count('users.id');

        // Churches that uploaded first sermon
        $churchesWithSermons = Church::where('churches.created_at', '>=', $startDate)
            ->join('sermons', 'churches.id', '=', 'sermons.church_id')
            ->distinct('churches.id')
            ->count('churches.id');
        
        $totalNewChurches = Church::where('created_at', '>=', $startDate)->count();

        return [
            'new_users' => $newUsers,
            'first_day_listeners' => $firstDayListeners,
            'first_day_listener_rate' => $newUsers > 0 
                ? round(($firstDayListeners / $newUsers) * 100, 1) 
                : 0,
            'activated_churches' => $churchesWithSermons,
            'church_activation_rate' => $totalNewChurches > 0 
                ? round(($churchesWithSermons / $totalNewChurches) * 100, 1) 
                : 0,
        ];
    }

    public function updatedPeriod()
    {
        // Reload data when period changes
    }
}
