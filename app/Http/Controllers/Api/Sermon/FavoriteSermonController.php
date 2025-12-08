<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\SermonResource;
use App\Models\Sermon;
use App\Models\SermonFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteSermonController extends Controller
{
    /**
     * Add a sermon to favorites
     *
     * @param int $sermonId
     * @return JsonResponse
     */
    public function addFavorite($sermonId): JsonResponse
    {
        try {
            $user = Auth::user();
            $sermon = Sermon::findOrFail($sermonId);

            // Only allow favoriting published sermons
            if (!$sermon->is_published) {
                return $this->errorResponse('Ce sermon n\'est pas disponible', 404);
            }

            // Check if already favorited
            if ($this->isFavoritedByUser($user->id, $sermonId)) {
                return $this->errorResponse('Ce sermon est déjà dans vos favoris', 400);
            }

            // Add to favorites
            $favorite = $this->createFavorite($user->id, $sermonId);

            Log::info('Sermon ajouté aux favoris', [
                'user_id' => $user->id,
                'sermon_id' => $sermonId,
            ]);

            return $this->successResponse(
                'Sermon ajouté aux favoris avec succès',
                [
                    'favorite_id' => $favorite->id,
                    'sermon' => [
                        'id' => $sermon->id,
                        'title' => $sermon->title,
                    ],
                ],
                201
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'ajout aux favoris', [
                'user_id' => Auth::id(),
                'sermon_id' => $sermonId,
            ]);
        }
    }

    /**
     * Remove a sermon from favorites
     *
     * @param int $sermonId
     * @return JsonResponse
     */
    public function removeFavorite($sermonId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Find the favorite
            $favorite = $this->findFavorite($user->id, $sermonId);

            if (!$favorite) {
                return $this->errorResponse('Ce sermon n\'est pas dans vos favoris', 404);
            }

            // Remove from favorites
            $favorite->delete();

            Log::info('Sermon retiré des favoris', [
                'user_id' => $user->id,
                'sermon_id' => $sermonId,
            ]);

            return $this->successResponse('Sermon retiré des favoris avec succès');
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'retrait des favoris', [
                'user_id' => Auth::id(),
                'sermon_id' => $sermonId,
            ]);
        }
    }

    /**
     * Toggle favorite status (add if not favorited, remove if already favorited)
     *
     * @param int $sermonId
     * @return JsonResponse
     */
    public function toggleFavorite($sermonId): JsonResponse
    {
        try {
            $user = Auth::user();
            $sermon = Sermon::findOrFail($sermonId);

            // Check if already favorited
            $favorite = $this->findFavorite($user->id, $sermonId);

            if ($favorite) {
                // Remove from favorites
                $favorite->delete();

                return $this->successResponse(
                    'Sermon retiré des favoris',
                    ['is_favorite' => false],
                    200
                );
            }

            // Add to favorites
            $this->createFavorite($user->id, $sermonId);

            return $this->successResponse(
                'Sermon ajouté aux favoris',
                ['is_favorite' => true],
                201
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'modification du favori', [
                'user_id' => Auth::id(),
                'sermon_id' => $sermonId,
            ]);
        }
    }

    /**
     * Get all favorite sermons for authenticated user
     *
     * @return JsonResponse
     */
    public function getFavorites(): JsonResponse
    {
        try {
            $user = Auth::user();

            $favorites = SermonFavorite::where('user_id', $user->id)
                ->with(['sermon' => function ($query) {
                    $query->with(['church', 'category']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format each favorite using formatFavoriteData helper method
            $sermons = $favorites->map(function ($favorite) {
                return $this->formatFavoriteData($favorite);
            });

            return $this->successResponse(
                'Liste des favoris récupérée avec succès',
                [
                    'total' => $sermons->count(),
                    'favorites' => $sermons,
                ]
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'récupération des favoris', [
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Check if a sermon is favorited by the authenticated user
     *
     * @param int $sermonId
     * @return JsonResponse
     */
    public function isFavorite($sermonId): JsonResponse
    {
        try {
            $user = Auth::user();
            $isFavorite = $this->isFavoritedByUser($user->id, $sermonId);

            return $this->successResponse(
                null,
                ['is_favorite' => $isFavorite]
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'vérification du favori', [
                'user_id' => Auth::id(),
                'sermon_id' => $sermonId,
            ]);
        }
    }

    // ============================================
    // Private Helper Methods (Réutilisables)
    // ============================================

    /**
     * Find a favorite by user and sermon IDs
     *
     * @param int $userId
     * @param int $sermonId
     * @return SermonFavorite|null
     */
    private function findFavorite(int $userId, int $sermonId): ?SermonFavorite
    {
        return SermonFavorite::where('user_id', $userId)
            ->where('sermon_id', $sermonId)
            ->first();
    }

    /**
     * Check if a sermon is favorited by a user
     *
     * @param int $userId
     * @param int $sermonId
     * @return bool
     */
    private function isFavoritedByUser(int $userId, int $sermonId): bool
    {
        return SermonFavorite::where('user_id', $userId)
            ->where('sermon_id', $sermonId)
            ->exists();
    }

    /**
     * Create a new favorite
     *
     * @param int $userId
     * @param int $sermonId
     * @return SermonFavorite
     */
    private function createFavorite(int $userId, int $sermonId): SermonFavorite
    {
        return SermonFavorite::create([
            'user_id' => $userId,
            'sermon_id' => $sermonId,
        ]);
    }

    /**
     * Format favorite data for response
     *
     * @param SermonFavorite $favorite
     * @return array
     */
    private function formatFavoriteData(SermonFavorite $favorite): array
    {
        return [
            'favorite_id' => $favorite->id,
            'favorited_at' => $favorite->created_at,
            'sermon' => new SermonResource($favorite->sermon),
        ];
    }

    /**
     * Return a success JSON response
     *
     * @param string|null $message
     * @param array|null $data
     * @param int $statusCode
     * @return JsonResponse
     */
    private function successResponse(?string $message = null, ?array $data = null, int $statusCode = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
