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

        return [
            'day_1' => [
                'users' => $day1Users,
                'active' => $day1Active,
                'rate' => $day1Users > 0 ? round(($day1Active / $day1Users) * 100, 1) : 0,
            ],
            'day_7' => [
                'users' => $day7Users,
                'active' => $day7Active,
                'rate' => $day7Users > 0 ? round(($day7Active / $day7Users) * 100, 1) : 0,
            ],
            'day_30' => [
                'users' => $day30Users,
                'active' => $day30Active,
                'rate' => $day30Users > 0 ? round(($day30Active / $day30Users) * 100, 1) : 0,
            ],
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

                return [
                    'role' => $item->role->name ?? 'N/A',
                    'total_users' => $item->total,
                    'active_users' => $activeCount,
                    'engagement_rate' => $item->total > 0 
                        ? round(($activeCount / $item->total) * 100, 1) 
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get top active users
     */
    private function getTopActiveUsers($startDate)
    {
        return SermonView::select('user_id', DB::raw('COUNT(*) as play_count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_id')
            ->orderBy('play_count', 'desc')
            ->take(10)
            ->with(['user.role'])
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'name' => $item->user->name ?? 'N/A',
                    'email' => $item->user->email ?? 'N/A',
                    'role' => $item->user->role->name ?? 'N/A',
                    'play_count' => $item->play_count,
                ];
            })
            ->toArray();
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
            'first_day_conversion' => $newUsers > 0 
                ? round(($firstDayListeners / $newUsers) * 100, 1) 
                : 0,
            'churches_with_sermons' => $churchesWithSermons,
            'total_new_churches' => $totalNewChurches,
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
