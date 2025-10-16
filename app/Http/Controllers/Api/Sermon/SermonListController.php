<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\SermonResource;
use App\Models\Sermon;
use App\Models\SermonView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SermonListController extends Controller
{
    /**
     * Get the 10 most recent sermons
     */
    public function getRecentSermons(): JsonResponse
    {
        try {
            $recentSermons = Sermon::with(['church', 'category'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            Log::info('Recent sermons retrieved', [
                'count' => $recentSermons->count(),
                'user_id' => Auth::id()
            ]);

            return $this->successResponse(
                SermonResource::collection($recentSermons),
                'Recent sermons retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des sermons récents.',
                [
                    'user' => Auth::user(),
                ]
            );
        }
    }

    /**
     * Get popular sermons based on popularity score
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPopularSermons(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10); // Default 10 sermons
            $minScore = $request->input('min_score', 0); // Minimum score filter

            $popularSermons = Sermon::with(['church', 'category'])
                ->withCount(['favoritedBy', 'views'])
                ->minimumPopularity($minScore)
                ->popular()
                ->take($limit)
                ->get();

            Log::info('Popular sermons retrieved', [
                'count' => $popularSermons->count(),
                'limit' => $limit,
                'min_score' => $minScore,
                'user_id' => Auth::id()
            ]);

            return $this->successResponse(
                SermonResource::collection($popularSermons),
                'Popular sermons retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des sermons populaires.',
                [
                    'user' => Auth::user(),
                ]
            );
        }
    }

    /**
     * Record a sermon view/play
     *
     * @param Request $request
     * @param Sermon $sermon
     * @return JsonResponse
     */
    public function recordSermonPlay(Request $request, Sermon $sermon): JsonResponse
    {
        try {
            $validated = $request->validate([
                'duration_played' => 'nullable|integer|min:0',
                'completed' => 'nullable|boolean',
            ]);

            // Create sermon view record
            $view = SermonView::create([
                'sermon_id' => $sermon->id,
                'user_id' => Auth::id(), // Can be null for anonymous users
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'duration_played' => $validated['duration_played'] ?? null,
                'completed' => $validated['completed'] ?? false,
                'played_at' => now(),
            ]);

            Log::info('Sermon view recorded', [
                'sermon_id' => $sermon->id,
                'user_id' => Auth::id(),
                'duration_played' => $validated['duration_played'] ?? 'N/A',
                'completed' => $validated['completed'] ?? false,
            ]);

            return $this->successResponse(
                [
                    'view_id' => $view->id,
                    'sermon' => [
                        'id' => $sermon->id,
                        'title' => $sermon->title,
                    ],
                ],
                'Sermon view recorded successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'enregistrement de la vue du sermon',
                [
                    'sermon_id' => $sermon->id,
                    'user_id' => Auth::id(),
                ]
            );
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
}
