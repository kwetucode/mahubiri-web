<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Donation;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Models\SermonView;
use App\Models\SermonFavorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminStatsController extends Controller
{
    /**
     * Get complete admin dashboard statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est admin
            $user = Auth::user();
            if (!$user->role || !$user->role->hasAdminPrivileges()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Droits administrateur requis.',
                ], 403);
            }

            $statistics = [
                'overview' => $this->getOverviewStats(),
                'growth' => $this->getGrowthStats(),
                'content' => $this->getContentStats(),
                'engagement' => $this->getEngagementStats(),
                'donations' => $this->getDonationStats(),
                'top_performers' => $this->getTopPerformers(),
                'recent_activity' => $this->getRecentActivity(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques administrateur récupérées avec succès',
                'data' => $statistics,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get quick overview statistics (for dashboard cards)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStats()
    {
        try {
            $user = Auth::user();
            if (!$user->role || !$user->role->hasAdminPrivileges()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $this->getOverviewStats(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();

        return [
            'total_users' => [
                'count' => User::count(),
                'this_month' => User::where('created_at', '>=', $startOfMonth)->count(),
                'last_month' => User::whereBetween('created_at', [
                    $lastMonth->copy()->startOfMonth(),
                    $lastMonth->copy()->endOfMonth()
                ])->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
            ],
            'total_churches' => [
                'count' => Church::count(),
                'active' => Church::where('is_active', true)->count(),
                'this_month' => Church::where('created_at', '>=', $startOfMonth)->count(),
            ],
            'total_preachers' => [
                'count' => PreacherProfile::count(),
                'active' => PreacherProfile::where('is_active', true)->count(),
                'this_month' => PreacherProfile::where('created_at', '>=', $startOfMonth)->count(),
            ],
            'total_sermons' => [
                'count' => Sermon::count(),
                'published' => Sermon::where('is_published', true)->count(),
                'draft' => Sermon::where('is_published', false)->count(),
                'this_month' => Sermon::where('created_at', '>=', $startOfMonth)->count(),
            ],
            'total_views' => [
                'count' => SermonView::count(),
                'this_month' => SermonView::where('created_at', '>=', $startOfMonth)->count(),
                'completed' => SermonView::where('completed', true)->count(),
            ],
            'total_favorites' => [
                'count' => SermonFavorite::count(),
                'this_month' => SermonFavorite::where('created_at', '>=', $startOfMonth)->count(),
            ],
        ];
    }

    /**
     * Get growth statistics over time
     */
    private function getGrowthStats(): array
    {
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'users' => User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'churches' => Church::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'preachers' => PreacherProfile::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'sermons' => Sermon::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'views' => SermonView::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            ];
        }

        // Calculate growth percentages
        $currentMonth = end($months);
        $previousMonth = $months[count($months) - 2] ?? $currentMonth;

        return [
            'monthly_data' => $months,
            'growth_rates' => [
                'users' => $this->calculateGrowthRate($previousMonth['users'], $currentMonth['users']),
                'churches' => $this->calculateGrowthRate($previousMonth['churches'], $currentMonth['churches']),
                'preachers' => $this->calculateGrowthRate($previousMonth['preachers'], $currentMonth['preachers']),
                'sermons' => $this->calculateGrowthRate($previousMonth['sermons'], $currentMonth['sermons']),
                'views' => $this->calculateGrowthRate($previousMonth['views'], $currentMonth['views']),
            ],
        ];
    }

    /**
     * Get content statistics
     */
    private function getContentStats(): array
    {
        // Sermons by category
        $sermonsByCategory = Sermon::select('category_sermon_id', DB::raw('count(*) as count'))
            ->with('categorySermon:id,name')
            ->groupBy('category_sermon_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_sermon_id,
                    'category_name' => $item->categorySermon?->name ?? 'Sans catégorie',
                    'count' => $item->count,
                ];
            });

        // Sermons by church
        $sermonsByChurch = Sermon::select('church_id', DB::raw('count(*) as count'))
            ->whereNotNull('church_id')
            ->with('church:id,name')
            ->groupBy('church_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'church_id' => $item->church_id,
                    'church_name' => $item->church?->name ?? 'Inconnu',
                    'count' => $item->count,
                ];
            });

        // Sermons by preacher
        $sermonsByPreacher = Sermon::select('preacher_profile_id', DB::raw('count(*) as count'))
            ->whereNotNull('preacher_profile_id')
            ->with('preacherProfile:id,ministry_name')
            ->groupBy('preacher_profile_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'preacher_id' => $item->preacher_profile_id,
                    'preacher_name' => $item->preacherProfile?->ministry_name ?? 'Inconnu',
                    'count' => $item->count,
                ];
            });

        // Average sermon duration
        $avgDuration = Sermon::whereNotNull('duration')->avg('duration');

        // Total storage used
        $totalStorage = Sermon::whereNotNull('size')->sum('size');

        return [
            'by_category' => $sermonsByCategory,
            'by_church' => $sermonsByChurch,
            'by_preacher' => $sermonsByPreacher,
            'average_duration' => [
                'seconds' => round($avgDuration ?? 0),
                'formatted' => gmdate('H:i:s', $avgDuration ?? 0),
            ],
            'total_storage' => [
                'bytes' => $totalStorage,
                'formatted' => $this->formatBytes($totalStorage),
            ],
        ];
    }

    /**
     * Get engagement statistics
     */
    private function getEngagementStats(): array
    {
        $now = Carbon::now();
        $last30Days = $now->copy()->subDays(30);
        $last7Days = $now->copy()->subDays(7);

        // Views per day (last 30 days)
        $dailyViews = SermonView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Active users (users who viewed a sermon in the last 30 days)
        $activeUsers = SermonView::where('created_at', '>=', $last30Days)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Average views per sermon
        $totalSermons = Sermon::where('is_published', true)->count();
        $totalViews = SermonView::count();
        $avgViewsPerSermon = $totalSermons > 0 ? round($totalViews / $totalSermons, 2) : 0;

        // Completion rate
        $completedViews = SermonView::where('completed', true)->count();
        $completionRate = $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0;

        // Average listening duration
        $avgListeningDuration = SermonView::whereNotNull('duration_played')->avg('duration_played');

        // Peak hours
        $peakHours = SermonView::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', $last30Days)
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'daily_views' => $dailyViews,
            'active_users' => [
                'last_30_days' => $activeUsers,
                'last_7_days' => SermonView::where('created_at', '>=', $last7Days)
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id'),
            ],
            'avg_views_per_sermon' => $avgViewsPerSermon,
            'completion_rate' => $completionRate,
            'avg_listening_duration' => [
                'seconds' => round($avgListeningDuration ?? 0),
                'formatted' => gmdate('H:i:s', $avgListeningDuration ?? 0),
            ],
            'peak_hours' => $peakHours->map(function ($item) {
                return [
                    'hour' => sprintf('%02d:00', $item->hour),
                    'count' => $item->count,
                ];
            }),
        ];
    }

    /**
     * Get donation statistics
     */
    private function getDonationStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();

        $totalDonations = Donation::where('status', 'completed')->sum('amount');
        $monthlyDonations = Donation::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');
        $lastMonthDonations = Donation::where('status', 'completed')
            ->whereBetween('created_at', [
                $lastMonth->copy()->startOfMonth(),
                $lastMonth->copy()->endOfMonth()
            ])
            ->sum('amount');

        // Donations by currency
        $byCurrency = Donation::select('currency', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
            ->where('status', 'completed')
            ->groupBy('currency')
            ->get();

        // Monthly donation trend
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'total' => Donation::where('status', 'completed')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('amount'),
                'count' => Donation::where('status', 'completed')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
            ];
        }

        // Top donors
        $topDonors = Donation::select('user_id', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
            ->where('status', 'completed')
            ->whereNotNull('user_id')
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user?->name ?? 'Anonyme',
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            });

        return [
            'total' => $totalDonations,
            'this_month' => $monthlyDonations,
            'last_month' => $lastMonthDonations,
            'growth_rate' => $this->calculateGrowthRate($lastMonthDonations, $monthlyDonations),
            'by_currency' => $byCurrency,
            'monthly_trend' => $monthlyTrend,
            'top_donors' => $topDonors,
            'average_donation' => [
                'amount' => Donation::where('status', 'completed')->avg('amount') ?? 0,
            ],
        ];
    }

    /**
     * Get top performers
     */
    private function getTopPerformers(): array
    {
        // Top churches by sermon count
        $topChurches = Church::withCount(['sermons' => function ($query) {
                $query->where('is_published', true);
            }])
            ->with(['sermonViews' => function ($query) {
                $query->select('sermon_views.id');
            }])
            ->where('is_active', true)
            ->orderByDesc('sermons_count')
            ->limit(10)
            ->get()
            ->map(function ($church) {
                return [
                    'id' => $church->id,
                    'name' => $church->name,
                    'sermons_count' => $church->sermons_count,
                    'total_views' => $church->sermonViews->count(),
                ];
            });

        // Top preachers by sermon count
        $topPreachers = PreacherProfile::withCount(['sermons' => function ($query) {
                $query->where('is_published', true);
            }])
            ->with('user:id,name')
            ->where('is_active', true)
            ->orderByDesc('sermons_count')
            ->limit(10)
            ->get()
            ->map(function ($preacher) {
                return [
                    'id' => $preacher->id,
                    'ministry_name' => $preacher->ministry_name,
                    'user_name' => $preacher->user?->name,
                    'sermons_count' => $preacher->sermons_count,
                ];
            });

        // Most viewed sermons
        $topSermons = Sermon::withCount('views')
            ->with(['church:id,name', 'preacherProfile:id,ministry_name'])
            ->where('is_published', true)
            ->orderByDesc('views_count')
            ->limit(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'publisher' => $sermon->church?->name ?? $sermon->preacherProfile?->ministry_name ?? 'Inconnu',
                    'views_count' => $sermon->views_count,
                ];
            });

        // Most favorited sermons
        $topFavorited = Sermon::withCount('favorites')
            ->with(['church:id,name', 'preacherProfile:id,ministry_name'])
            ->where('is_published', true)
            ->orderByDesc('favorites_count')
            ->limit(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'publisher' => $sermon->church?->name ?? $sermon->preacherProfile?->ministry_name ?? 'Inconnu',
                    'favorites_count' => $sermon->favorites_count,
                ];
            });

        return [
            'top_churches' => $topChurches,
            'top_preachers' => $topPreachers,
            'top_viewed_sermons' => $topSermons,
            'top_favorited_sermons' => $topFavorited,
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        // Recent users
        $recentUsers = User::with('role:id,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->name,
                    'created_at' => $user->created_at->toDateTimeString(),
                ];
            });

        // Recent churches
        $recentChurches = Church::with('createdBy:id,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($church) {
                return [
                    'id' => $church->id,
                    'name' => $church->name,
                    'created_by' => $church->createdBy?->name,
                    'created_at' => $church->created_at->toDateTimeString(),
                ];
            });

        // Recent sermons
        $recentSermons = Sermon::with(['church:id,name', 'preacherProfile:id,ministry_name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'publisher' => $sermon->church?->name ?? $sermon->preacherProfile?->ministry_name ?? 'Inconnu',
                    'is_published' => $sermon->is_published,
                    'created_at' => $sermon->created_at->toDateTimeString(),
                ];
            });

        // Recent donations
        $recentDonations = Donation::with(['user:id,name', 'church:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'amount' => $donation->amount,
                    'currency' => $donation->currency,
                    'donor' => $donation->user?->name ?? 'Anonyme',
                    'recipient' => $donation->church?->name ?? 'Inconnu',
                    'status' => $donation->status,
                    'created_at' => $donation->created_at->toDateTimeString(),
                ];
            });

        return [
            'recent_users' => $recentUsers,
            'recent_churches' => $recentChurches,
            'recent_sermons' => $recentSermons,
            'recent_donations' => $recentDonations,
        ];
    }

    /**
     * Get geographical distribution
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function geographical()
    {
        try {
            $user = Auth::user();
            if (!$user->role || !$user->role->hasAdminPrivileges()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }

            // Churches by country
            $churchesByCountry = Church::select('country_name', DB::raw('count(*) as count'))
                ->whereNotNull('country_name')
                ->groupBy('country_name')
                ->orderByDesc('count')
                ->get();

            // Preachers by country
            $preachersByCountry = PreacherProfile::select('country_name', DB::raw('count(*) as count'))
                ->whereNotNull('country_name')
                ->groupBy('country_name')
                ->orderByDesc('count')
                ->get();

            // Churches by city
            $churchesByCity = Church::select('city', 'country_name', DB::raw('count(*) as count'))
                ->whereNotNull('city')
                ->groupBy('city', 'country_name')
                ->orderByDesc('count')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'churches_by_country' => $churchesByCountry,
                    'preachers_by_country' => $preachersByCountry,
                    'churches_by_city' => $churchesByCity,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate growth rate between two values
     */
    private function calculateGrowthRate($previous, $current): array
    {
        if ($previous == 0) {
            $rate = $current > 0 ? 100 : 0;
        } else {
            $rate = round((($current - $previous) / $previous) * 100, 2);
        }

        return [
            'percentage' => $rate,
            'trend' => $rate > 0 ? 'up' : ($rate < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
