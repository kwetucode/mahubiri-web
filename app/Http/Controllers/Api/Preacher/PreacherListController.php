<?php

namespace App\Http\Controllers\Api\Preacher;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\PreacherProfileResource;
use App\Http\Resources\SermonResource;
use App\Models\PreacherProfile;
use Illuminate\Http\JsonResponse;

class PreacherListController extends Controller
{
    /**
     * Get the latest preacher profiles (default 5, customizable with limit parameter)
     *
     * @return JsonResponse
     */
    public function getLatestPreachers(): JsonResponse
    {
        try {
            $limit = request()->input('limit', 5);

            $preachers = PreacherProfile::active()->with('user')
                ->withCount(['sermons' => function ($query) {
                    $query->where('is_published', true);
                }])
                ->withCount(['sermonViews as total_views'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => PreacherProfileResource::collection($preachers),
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la récupération des derniers prédicateurs');
        }
    }

    /**
     * Get sermons by preacher profile ID
     *
     * @param int $preacherId
     * @return JsonResponse
     */
    public function getSermonsByPreacher(int $preacherId): JsonResponse
    {
        try {
            $preacher = PreacherProfile::active()->findOrFail($preacherId);

            $perPage = request()->input('per_page', 15);

            $sermons = $preacher->sermons()
                ->where('is_published', true)
                ->with(['church', 'category', 'currentUserFavorite'])
                ->withCount('views')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => SermonResource::collection($sermons),
                'pagination' => [
                    'current_page' => $sermons->currentPage(),
                    'last_page' => $sermons->lastPage(),
                    'per_page' => $sermons->perPage(),
                    'total' => $sermons->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la récupération des sermons du prédicateur');
        }
    }
}
