<?php

namespace App\Http\Controllers\Api\Church;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Sermon;
use App\Models\SermonView;
use App\Models\SermonFavorite;
use App\Models\User;
use App\Http\Resources\ChurchStatisticsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChurchStatisticsController extends Controller
{
    /**
     * Get comprehensive church statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChurchStatistics(Request $request)
    {
        try {
            // Vérifier que l'utilisateur connecté possède une église
            $church = Church::where('created_by', Auth::id())->first();

            if (!$church) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
                ], 404);
            }

            // Récupérer les statistiques
            $statistics = [
                'church_info' => [
                    'id' => $church->id,
                    'name' => $church->name,
                    'created_at' => $church->created_at,
                ],
                'sermon_stats' => $this->getSermonStatistics($church->id),
                'listening_stats' => $this->getListeningStatistics($church->id),
                'user_engagement' => $this->getUserEngagementStatistics($church->id),
                'publication_analysis' => $this->getPublicationAnalysis($church->id),
                'top_sermons' => $this->getTopSermons($church->id),
                'recent_activity' => $this->getRecentActivity($church->id),
                'disk_usage' => $this->getDiskUsageStatistics($church->id),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => new ChurchStatisticsResource($statistics),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sermon statistics for the church
     *
     * @param int $churchId
     * @return array
     */
    private function getSermonStatistics(int $churchId): array
    {
        $totalSermons = Sermon::where('church_id', $churchId)->count();
        $publishedSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)->count();
        $draftSermons = $totalSermons - $publishedSermons;

        // Sermons par mois (12 derniers mois) - only published
        $monthlySermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'count' => $item->count,
                ];
            })
            ->toArray();

        return [
            'total_sermons' => $totalSermons,
            'published_sermons' => $publishedSermons,
            'draft_sermons' => $draftSermons,
            'monthly_breakdown' => $monthlySermons,
        ];
    }

    /**
     * Get listening statistics for the church
     *
     * @param int $churchId
     * @return array
     */
    private function getListeningStatistics(int $churchId): array
    {
        // Total des écoutes
        $totalListens = SermonView::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })->count();

        // Écoutes uniques (utilisateurs distincts)
        $uniqueListeners = SermonView::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })->distinct('user_id')->count('user_id');

        // Écoutes par mois (12 derniers mois)
        $monthlyListens = SermonView::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'count' => $item->count,
                ];
            })
            ->toArray();

        // Moyenne d'écoutes par sermon - only published sermons
        $publishedSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)->count();
        $avgListensPerSermon = $totalListens > 0 && $publishedSermons > 0
            ? round($totalListens / $publishedSermons, 2)
            : 0;

        return [
            'total_listens' => $totalListens,
            'unique_listeners' => $uniqueListeners,
            'avg_listens_per_sermon' => $avgListensPerSermon,
            'monthly_listens' => $monthlyListens,
        ];
    }

    /**
     * Get user engagement statistics
     *
     * @param int $churchId
     * @return array
     */
    private function getUserEngagementStatistics(int $churchId): array
    {
        // Nombre total de favoris pour les sermons de cette église
        $totalFavorites = SermonFavorite::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })->count();

        // Utilisateurs qui ont mis en favori au moins un sermon
        $usersWithFavorites = SermonFavorite::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })->distinct('user_id')->count('user_id');

        // Top 5 des utilisateurs les plus actifs (qui écoutent le plus)
        $topListeners = SermonView::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })
            ->select('user_id', DB::raw('COUNT(*) as listen_count'))
            ->groupBy('user_id')
            ->orderBy('listen_count', 'desc')
            ->limit(5)
            ->with('user:id,name,email')
            ->get()
            ->map(function ($item) {
                if (!$item->user) {
                    return [
                        'user' => [
                            'id' => null,
                            'name' => 'Utilisateur anonyme',
                            'email' => null,
                        ],
                        'listen_count' => $item->listen_count,
                    ];
                }
                return [
                    'user' => [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ],
                    'listen_count' => $item->listen_count,
                ];
            })
            ->toArray();

        return [
            'total_favorites' => $totalFavorites,
            'users_with_favorites' => $usersWithFavorites,
            'top_listeners' => $topListeners,
        ];
    }

    /**
     * Get publication analysis for charts
     *
     * @param int $churchId
     * @return array
     */
    private function getPublicationAnalysis(int $churchId): array
    {
        // Analyse annuelle (5 dernières années) - only published sermons
        $yearlyAnalysis = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->where('created_at', '>=', Carbon::now()->subYears(5))
            ->selectRaw('YEAR(created_at) as year, COUNT(*) as sermon_count')
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'sermon_count' => $item->sermon_count,
                ];
            })
            ->toArray();

        // Analyse mensuelle (12 derniers mois avec écoutes)
        $monthlyAnalysis = DB::table('sermons')
            ->leftJoin('sermon_views', 'sermons.id', '=', 'sermon_views.sermon_id')
            ->where('sermons.church_id', $churchId)
            ->where('sermons.created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('
                YEAR(sermons.created_at) as year,
                MONTH(sermons.created_at) as month,
                COUNT(DISTINCT sermons.id) as sermon_count,
                COUNT(sermon_views.id) as listen_count
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'sermon_count' => $item->sermon_count,
                    'listen_count' => $item->listen_count,
                ];
            })
            ->toArray();

        // Jour de la semaine le plus actif pour les publications - only published sermons
        $weekdayAnalysis = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->selectRaw('DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count')
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($item) {
                $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                return [
                    'day_number' => $item->day_of_week,
                    'day_name' => $days[$item->day_of_week - 1],
                    'count' => $item->count,
                ];
            })
            ->toArray();

        return [
            'yearly_analysis' => $yearlyAnalysis,
            'monthly_analysis' => $monthlyAnalysis,
            'weekday_analysis' => $weekdayAnalysis,
        ];
    }

    /**
     * Get top performing sermons
     *
     * @param int $churchId
     * @return array
     */
    private function getTopSermons(int $churchId): array
    {
        $topSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->withCount(['views', 'favoritedBy'])
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($sermon) {
                return [
                    'id' => $sermon->id,
                    'title' => $sermon->title,
                    'views_count' => $sermon->views_count,
                    'favorites_count' => $sermon->favorited_by_count,
                    'created_at' => $sermon->created_at,
                ];
            })
            ->toArray();

        return $topSermons;
    }

    /**
     * Get recent activity
     *
     * @param int $churchId
     * @return array
     */
    private function getRecentActivity(int $churchId): array
    {
        // Derniers sermons créés - only published sermons
        $recentSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->select('id', 'title', 'created_at')
            ->get()
            ->toArray();

        // Dernières écoutes
        $recentListens = SermonView::whereHas('sermon', function ($query) use ($churchId) {
            $query->where('church_id', $churchId);
        })
            ->with(['user:id,name', 'sermon:id,title'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($listen) {
                return [
                    'user_name' => $listen->user ? $listen->user->name : 'Utilisateur anonyme',
                    'sermon_title' => $listen->sermon->title,
                    'sermon_id' => $listen->sermon->id,
                    'listened_at' => $listen->created_at,
                ];
            })
            ->toArray();

        return [
            'recent_sermons' => $recentSermons,
            'recent_listens' => $recentListens,
        ];
    }

    /**
     * Get quick summary stats
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuickStats(Request $request)
    {
        try {
            $church = Church::where('created_by', Auth::id())->first();

            if (!$church) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
                ], 404);
            }

            $quickStats = [
                'total_sermons' => Sermon::where('church_id', $church->id)->count(),
                'published_sermons' => Sermon::where('church_id', $church->id)
                    ->where('is_published', true)->count(),
                'total_listens' => SermonView::whereHas('sermon', function ($query) use ($church) {
                    $query->where('church_id', $church->id);
                })->count(),
                'unique_listeners' => SermonView::whereHas('sermon', function ($query) use ($church) {
                    $query->where('church_id', $church->id);
                })->distinct('user_id')->count('user_id'),
                'total_favorites' => SermonFavorite::whereHas('sermon', function ($query) use ($church) {
                    $query->where('church_id', $church->id);
                })->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $quickStats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test endpoint to check basic functionality
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testStats(Request $request)
    {
        try {
            $church = Church::where('created_by', Auth::id())->first();

            if (!$church) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
                ], 404);
            }

            // Test basic queries
            $testData = [
                'church_id' => $church->id,
                'church_name' => $church->name,
                'total_sermons' => Sermon::where('church_id', $church->id)->count(),
                'sermon_columns' => Sermon::first() ? array_keys(Sermon::first()->toArray()) : [],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Test réussi',
                'data' => $testData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur dans le test: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get disk usage statistics for church sermons
     * Quota: 3 GB per church
     *
     * @param int $churchId
     * @return array
     */
    private function getDiskUsageStatistics(int $churchId): array
    {
        // Quota en octets (3 GB = 3 * 1024 * 1024 * 1024)
        $quotaBytes = 3 * 1024 * 1024 * 1024; // 3221225472 octets
        
        // Calculer la taille totale des sermons de l'église
        $totalSizeBytes = Sermon::where('church_id', $churchId)
            ->sum('size') ?? 0;

        // Calculer les statistiques
        $usedGB = round($totalSizeBytes / (1024 * 1024 * 1024), 2);
        $quotaGB = 3.0;
        $usedPercentage = $totalSizeBytes > 0 
            ? round(($totalSizeBytes / $quotaBytes) * 100, 2) 
            : 0;
        $remainingBytes = max(0, $quotaBytes - $totalSizeBytes);
        $remainingGB = round($remainingBytes / (1024 * 1024 * 1024), 2);
        $remainingPercentage = round(100 - $usedPercentage, 2);

        // Compter le nombre de sermons
        $totalSermons = Sermon::where('church_id', $churchId)->count();
        $publishedSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->count();

        // Taille moyenne par sermon
        $avgSizeMB = $totalSermons > 0 
            ? round(($totalSizeBytes / $totalSermons) / (1024 * 1024), 2)
            : 0;

        // Déterminer le statut
        $status = 'normal';
        if ($usedPercentage >= 90) {
            $status = 'critical';
        } elseif ($usedPercentage >= 75) {
            $status = 'warning';
        }

        return [
            'quota' => [
                'total_bytes' => $quotaBytes,
                'total_gb' => $quotaGB,
            ],
            'used' => [
                'bytes' => $totalSizeBytes,
                'mb' => round($totalSizeBytes / (1024 * 1024), 2),
                'gb' => $usedGB,
                'percentage' => $usedPercentage,
            ],
            'remaining' => [
                'bytes' => $remainingBytes,
                'mb' => round($remainingBytes / (1024 * 1024), 2),
                'gb' => $remainingGB,
                'percentage' => $remainingPercentage,
            ],
            'sermons' => [
                'total' => $totalSermons,
                'published' => $publishedSermons,
                'avg_size_mb' => $avgSizeMB,
            ],
            'status' => $status,
            'messages' => [
                'normal' => 'Espace de stockage disponible',
                'warning' => 'Attention: vous approchez de la limite de stockage',
                'critical' => 'Critique: espace de stockage presque épuisé',
            ],
            'current_message' => $status === 'critical' 
                ? 'Critique: espace de stockage presque épuisé'
                : ($status === 'warning' 
                    ? 'Attention: vous approchez de la limite de stockage'
                    : 'Espace de stockage disponible'),
        ];
    }
}
