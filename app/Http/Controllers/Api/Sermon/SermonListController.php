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
                ->take(5)
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
            $minCompleteViews = $request->input('min_complete_views', 2); // Minimum completed views (default: 2)

            // Get sermons with at least X unique users who completed listening
            $popularSermons = Sermon::with(['church', 'category'])
                ->withCount(['favoritedBy', 'views'])
                ->addSelect([
                    'completed_unique_users' => SermonView::selectRaw('COUNT(DISTINCT user_id)')
                        ->whereColumn('sermon_id', 'sermons.id')
                        ->where('completed', true)
                ])
                ->having('completed_unique_users', '>=', $minCompleteViews)
                ->where('popularity_score', '>=', $minScore)
                ->orderBy('popularity_score', 'desc')
                ->take($limit)
                ->get();

            Log::info('Popular sermons retrieved', [
                'count' => $popularSermons->count(),
                'limit' => $limit,
                'min_score' => $minScore,
                'min_complete_views' => $minCompleteViews,
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
     * Get all sermons by category
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getSermonsByCategory(int $categoryId): JsonResponse
    {
        try {
            // Get sermons from the specified category (paginated, 10 per page)
            $sermons = Sermon::with(['church', 'category'])
                ->where('category_sermon_id', $categoryId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            Log::info('Sermons by category retrieved', [
                'category_id' => $categoryId,
                'count' => $sermons->count(),
                'total' => $sermons->total(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'sermons' => SermonResource::collection($sermons),
                    'pagination' => [
                        'current_page' => $sermons->currentPage(),
                        'last_page' => $sermons->lastPage(),
                        'per_page' => $sermons->perPage(),
                        'total' => $sermons->total(),
                        'from' => $sermons->firstItem(),
                        'to' => $sermons->lastItem(),
                    ]
                ],
                'message' => 'Sermons by category retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des sermons par catégorie.',
                [
                    'category_id' => $categoryId,
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Get a sermon with related sermons from the same category
     *
     * @param Sermon $sermon
     * @return JsonResponse
     */
    public function getSermonWithRelated(Sermon $sermon): JsonResponse
    {
        try {
            // Load the sermon with relationships
            $sermon->load(['church', 'category', 'favoritedBy', 'views']);

            // Get related sermons from the same category (paginated, 10 per page)
            $relatedSermons = Sermon::with(['church', 'category'])
                ->where('category_sermon_id', $sermon->category_sermon_id)
                ->where('id', '!=', $sermon->id) // Exclude current sermon
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            Log::info('Sermon with related retrieved', [
                'sermon_id' => $sermon->id,
                'category_id' => $sermon->category_sermon_id,
                'related_count' => $relatedSermons->count(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'sermon' => new SermonResource($sermon),
                    'related_sermons' => [
                        'data' => SermonResource::collection($relatedSermons),
                        'pagination' => [
                            'current_page' => $relatedSermons->currentPage(),
                            'last_page' => $relatedSermons->lastPage(),
                            'per_page' => $relatedSermons->perPage(),
                            'total' => $relatedSermons->total(),
                            'from' => $relatedSermons->firstItem(),
                            'to' => $relatedSermons->lastItem(),
                        ]
                    ]
                ],
                'message' => 'Sermon and related sermons retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération du sermon et des sermons connexes.',
                [
                    'sermon_id' => $sermon->id,
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Get all sermons by church
     *
     * @param int $churchId
     * @return JsonResponse
     */
    public function getSermonsByChurch(int $churchId): JsonResponse
    {
        try {
            // Get sermons from the specified church (paginated, 10 per page)
            $sermons = Sermon::with(['church', 'category'])
                ->where('church_id', $churchId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            Log::info('Sermons by church retrieved', [
                'church_id' => $churchId,
                'count' => $sermons->count(),
                'total' => $sermons->total(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'sermons' => SermonResource::collection($sermons),
                    'pagination' => [
                        'current_page' => $sermons->currentPage(),
                        'last_page' => $sermons->lastPage(),
                        'per_page' => $sermons->perPage(),
                        'total' => $sermons->total(),
                        'from' => $sermons->firstItem(),
                        'to' => $sermons->lastItem(),
                    ]
                ],
                'message' => 'Sermons by church retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des sermons par église.',
                [
                    'church_id' => $churchId,
                    'user_id' => Auth::id(),
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
