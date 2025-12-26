<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Models\SermonFavorite;
use App\Models\SermonView;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserStatsController extends Controller
{
    /**
     * Get current user statistics for dashboard widget
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

            // Get basic user info
            $userInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url ? asset($user->avatar_url) : null,
            ];

            // Count sermons listened (distinct sermons the user has viewed)
            $sermonsListenedCount = SermonView::where('user_id', $user->id)
                ->distinct('sermon_id')
                ->count('sermon_id');

            // Count total listening time (sum of duration_played)
            $totalListeningTime = SermonView::where('user_id', $user->id)
                ->whereNotNull('duration_played')
                ->sum('duration_played');

            // Count favorites
            $favoritesCount = SermonFavorite::where('user_id', $user->id)->count();

            // Format listening time (convert seconds to human readable format)
            $formattedListeningTime = $this->formatDuration($totalListeningTime);

            // Get additional stats
            $completedSermons = SermonView::where('user_id', $user->id)
                ->where('completed', true)
                ->distinct('sermon_id')
                ->count('sermon_id');

            // Get most recent activity
            $lastActivity = SermonView::where('user_id', $user->id)
                ->latest('played_at')
                ->first();

            $stats = [
                'user' => $userInfo,
                'listening_stats' => [
                    'sermons_listened_count' => $sermonsListenedCount,
                    'total_listening_time_seconds' => $totalListeningTime,
                    'total_listening_time_formatted' => $formattedListeningTime,
                    'favorites_count' => $favoritesCount,
                    'completed_sermons_count' => $completedSermons,
                ],
                'activity' => [
                    'last_activity_at' => $lastActivity ? $lastActivity->played_at : null,
                    'is_active_listener' => $sermonsListenedCount > 0,
                ]
            ];

            Log::info('User stats retrieved successfully', [
                'user_id' => $user->id,
                'sermons_count' => $sermonsListenedCount,
                'favorites_count' => $favoritesCount,
            ]);

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

            // Get listening stats by month (last 6 months)
            $monthlyStats = SermonView::select(
                DB::raw('DATE_FORMAT(played_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT sermon_id) as sermons_count'),
                DB::raw('SUM(duration_played) as total_duration')
            )
                ->where('user_id', $user->id)
                ->where('played_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            // Get favorite genres/categories
            $favoriteCategories = SermonFavorite::join('sermons', 'sermon_favorites.sermon_id', '=', 'sermons.id')
                ->join('category_sermons', 'sermons.category_sermon_id', '=', 'category_sermons.id')
                ->select('category_sermons.name', DB::raw('COUNT(*) as count'))
                ->where('sermon_favorites.user_id', $user->id)
                ->groupBy('category_sermons.id', 'category_sermons.name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Get favorite churches
            $favoriteChurches = SermonFavorite::join('sermons', 'sermon_favorites.sermon_id', '=', 'sermons.id')
                ->join('churches', 'sermons.church_id', '=', 'churches.id')
                ->where('churches.is_active', true) // Only active churches
                ->select('churches.name', 'churches.id', DB::raw('COUNT(*) as favorites_count'))
                ->where('sermon_favorites.user_id', $user->id)
                ->groupBy('churches.id', 'churches.name')
                ->orderBy('favorites_count', 'desc')
                ->limit(5)
                ->get();

            $detailedStats = [
                'monthly_listening' => $monthlyStats->map(function ($stat) {
                    return [
                        'month' => $stat->month,
                        'sermons_listened' => $stat->sermons_count,
                        'total_duration_seconds' => $stat->total_duration ?? 0,
                        'total_duration_formatted' => $this->formatDuration($stat->total_duration ?? 0),
                    ];
                }),
                'favorite_categories' => $favoriteCategories->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'favorites_count' => $category->count,
                    ];
                }),
                'favorite_churches' => $favoriteChurches->map(function ($church) {
                    return [
                        'id' => $church->id,
                        'name' => $church->name,
                        'favorites_count' => $church->favorites_count,
                    ];
                }),
            ];

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
     * Format duration from seconds to human readable format
     *
     * @param int|null $seconds
     * @return string
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
            'message' => $message
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
            'errors' => $errors
        ], $status);
    }
}
