<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Models\SermonFavorite;
use App\Models\SermonView;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    /**
     * Get current user statistics for dashboard widget
     * Optimized: single aggregation query instead of multiple separate queries
     *
     * @return JsonResponse
     */
    public function getUserStats(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(
                    'Utilisateur non authentifié',
                    [],
                    401
                );
            }

            // Cache stats for 5 minutes per user to reduce DB load
            $cacheKey = "user_stats_{$user->id}";
            $stats = Cache::remember($cacheKey, 300, function () use ($user) {
                return $this->buildUserStats($user);
            });

            return $this->successResponse(
                $stats,
                'Statistiques utilisateur récupérées avec succès'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des statistiques utilisateur',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Get detailed listening statistics for the user
     *
     * @return JsonResponse
     */
    public function getDetailedStats(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(
                    'Utilisateur non authentifié',
                    [],
                    401
                );
            }

            $cacheKey = "user_detailed_stats_{$user->id}";
            $detailedStats = Cache::remember($cacheKey, 300, function () use ($user) {
                return $this->buildDetailedStats($user);
            });

            return $this->successResponse(
                $detailedStats,
                'Statistiques détaillées récupérées avec succès'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des statistiques détaillées',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Build user stats with optimized single aggregation query
     */
    private function buildUserStats($user): array
    {
        // Single aggregation query instead of 3 separate queries
        $listeningAgg = SermonView::where('user_id', $user->id)
            ->select([
                DB::raw('COUNT(DISTINCT sermon_id) as sermons_listened'),
                DB::raw('COALESCE(SUM(duration_played), 0) as total_duration'),
                DB::raw('COUNT(DISTINCT CASE WHEN completed = 1 THEN sermon_id END) as completed_sermons'),
                DB::raw('MAX(played_at) as last_played_at'),
            ])
            ->first();

        $favoritesCount = SermonFavorite::where('user_id', $user->id)->count();

        $sermonsListened = (int) ($listeningAgg->sermons_listened ?? 0);
        $totalDuration = (int) ($listeningAgg->total_duration ?? 0);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url ? asset($user->avatar_url) : null,
            ],
            'listening_stats' => [
                'sermons_listened_count' => $sermonsListened,
                'total_listening_time_seconds' => $totalDuration,
                'total_listening_time_formatted' => $this->formatDuration($totalDuration),
                'favorites_count' => $favoritesCount,
                'completed_sermons_count' => (int) ($listeningAgg->completed_sermons ?? 0),
            ],
            'activity' => [
                'last_activity_at' => $listeningAgg->last_played_at,
                'is_active_listener' => $sermonsListened > 0,
            ],
        ];
    }

    /**
     * Build detailed stats with optimized queries
     */
    private function buildDetailedStats($user): array
    {
        // Monthly listening stats (last 6 months)
        $monthlyStats = SermonView::select(
            DB::raw('DATE_FORMAT(played_at, "%Y-%m") as month'),
            DB::raw('COUNT(DISTINCT sermon_id) as sermons_count'),
            DB::raw('COALESCE(SUM(duration_played), 0) as total_duration')
        )
            ->where('user_id', $user->id)
            ->where('played_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Favorite categories
        $favoriteCategories = SermonFavorite::join('sermons', 'sermon_favorites.sermon_id', '=', 'sermons.id')
            ->join('category_sermons', 'sermons.category_sermon_id', '=', 'category_sermons.id')
            ->select('category_sermons.name', DB::raw('COUNT(*) as count'))
            ->where('sermon_favorites.user_id', $user->id)
            ->groupBy('category_sermons.id', 'category_sermons.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Favorite churches (only active)
        $favoriteChurches = SermonFavorite::join('sermons', 'sermon_favorites.sermon_id', '=', 'sermons.id')
            ->join('churches', 'sermons.church_id', '=', 'churches.id')
            ->where('churches.is_active', true)
            ->select('churches.name', 'churches.id', DB::raw('COUNT(*) as favorites_count'))
            ->where('sermon_favorites.user_id', $user->id)
            ->groupBy('churches.id', 'churches.name')
            ->orderBy('favorites_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'monthly_listening' => $monthlyStats->map(fn($stat) => [
                'month' => $stat->month,
                'sermons_listened' => $stat->sermons_count,
                'total_duration_seconds' => (int) ($stat->total_duration ?? 0),
                'total_duration_formatted' => $this->formatDuration((int) ($stat->total_duration ?? 0)),
            ]),
            'favorite_categories' => $favoriteCategories->map(fn($cat) => [
                'name' => $cat->name,
                'favorites_count' => $cat->count,
            ]),
            'favorite_churches' => $favoriteChurches->map(fn($church) => [
                'id' => $church->id,
                'name' => $church->name,
                'favorites_count' => $church->favorites_count,
            ]),
        ];
    }

    /**
     * Format duration from seconds to human readable format
     */
    private function formatDuration(?int $seconds): string
    {
        if (!$seconds || $seconds <= 0) {
            return '0 min';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dmin', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%dmin %02ds', $minutes, $remainingSeconds);
        } else {
            return sprintf('%ds', $remainingSeconds);
        }
    }

    /**
     * Return a standardized success response
     */
    private function successResponse($data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    /**
     * Return a standardized error response
     */
    private function errorResponse(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
