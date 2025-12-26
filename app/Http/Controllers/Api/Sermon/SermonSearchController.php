<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Http\Resources\SermonResource;
use App\Http\Resources\SermonSearchResource;
use App\Models\Sermon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SermonSearchController extends Controller
{
    /**
     * Search sermons by multiple criteria
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'preacher_name' => 'nullable|string|max:255',
                'church_name' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1'
            ]);

            $query = Sermon::with(['church', 'category'])
                ->published();

            // General search query (searches across title, preacher_name, description, category name)
            if ($request->filled('query')) {
                $searchTerm = $request->input('query');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('preacher_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('category', function ($catQuery) use ($searchTerm) {
                            $catQuery->where('name', 'LIKE', "%{$searchTerm}%");
                        });
                });
            }

            // Specific title search
            if ($request->filled('title')) {
                $query->where('title', 'LIKE', '%' . $request->input('title') . '%');
            }

            // Specific preacher name search
            if ($request->filled('preacher_name')) {
                $query->where('preacher_name', 'LIKE', '%' . $request->input('preacher_name') . '%');
            }

            // Category search
            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->input('category') . '%');
                });
            }

            // Church name search
            if ($request->filled('church_name')) {
                $query->whereHas('church', function ($q) use ($request) {
                    $q->where('is_active', true)
                      ->where('name', 'LIKE', '%' . $request->input('church_name') . '%');
                });
            }

            // Order by relevance (popular sermons first, then newest)
            $query->orderBy('popularity_score', 'desc')
                ->orderBy('created_at', 'desc');

            // Pagination
            $limit = $request->input('limit', 20);
            $sermons = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Sermons retrieved successfully',
                'data' => [
                    'sermons' => SermonResource::collection($sermons->items()),
                    'pagination' => [
                        'current_page' => $sermons->currentPage(),
                        'last_page' => $sermons->lastPage(),
                        'per_page' => $sermons->perPage(),
                        'total' => $sermons->total(),
                        'from' => $sermons->firstItem(),
                        'to' => $sermons->lastItem(),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching sermons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'filters' => 'required|array',
                'filters.*.field' => 'required|string|in:title,preacher_name,category,church_name,description',
                'filters.*.operator' => 'required|string|in:like,equals,starts_with,ends_with',
                'filters.*.value' => 'required|string|max:255',
                'sort_by' => 'nullable|string|in:title,created_at,popularity_score,preacher_name',
                'sort_direction' => 'nullable|string|in:asc,desc',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = Sermon::with(['church', 'category'])
                ->published();

            // Apply filters
            foreach ($request->input('filters', []) as $filter) {
                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'];

                switch ($field) {
                    case 'title':
                    case 'preacher_name':
                    case 'description':
                        $this->applyFieldFilter($query, $field, $operator, $value);
                        break;

                    case 'category':
                        $query->whereHas('category', function ($q) use ($operator, $value) {
                            $this->applyFieldFilter($q, 'name', $operator, $value);
                        });
                        break;

                    case 'church_name':
                        $query->whereHas('church', function ($q) use ($operator, $value) {
                            $q->where('is_active', true);
                            $this->applyFieldFilter($q, 'name', $operator, $value);
                        });
                        break;
                }
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'popularity_score');
            $sortDirection = $request->input('sort_direction', 'desc');

            if ($sortBy === 'popularity_score') {
                $query->orderBy('popularity_score', $sortDirection)
                    ->orderBy('created_at', 'desc');
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $limit = $request->input('limit', 20);
            $sermons = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Advanced search completed successfully',
                'data' => [
                    'sermons' => SermonResource::collection($sermons->items()),
                    'pagination' => [
                        'current_page' => $sermons->currentPage(),
                        'last_page' => $sermons->lastPage(),
                        'per_page' => $sermons->perPage(),
                        'total' => $sermons->total(),
                        'from' => $sermons->firstItem(),
                        'to' => $sermons->lastItem(),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in advanced search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions based on partial input
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'field' => 'required|string|in:title,preacher_name,category,church_name',
                'query' => 'required|string|min:2|max:100',
                'limit' => 'nullable|integer|min:1|max:20'
            ]);

            $field = $request->input('field');
            $searchQuery = $request->input('query');
            $limit = $request->input('limit', 10);

            $suggestions = [];

            switch ($field) {
                case 'title':
                    $suggestions = Sermon::published()
                        ->where('title', 'LIKE', "%{$searchQuery}%")
                        ->select('title as suggestion')
                        ->distinct()
                        ->limit($limit)
                        ->pluck('suggestion')
                        ->toArray();
                    break;

                case 'preacher_name':
                    $suggestions = Sermon::published()
                        ->where('preacher_name', 'LIKE', "%{$searchQuery}%")
                        ->select('preacher_name as suggestion')
                        ->distinct()
                        ->limit($limit)
                        ->pluck('suggestion')
                        ->toArray();
                    break;

                case 'category':
                    $suggestions = \App\Models\CategorySermon::where('name', 'LIKE', "%{$searchQuery}%")
                        ->select('name as suggestion')
                        ->limit($limit)
                        ->pluck('suggestion')
                        ->toArray();
                    break;

                case 'church_name':
                    $suggestions = \App\Models\Church::active() // Only active churches
                        ->where('name', 'LIKE', "%{$searchQuery}%")
                        ->select('name as suggestion')
                        ->limit($limit)
                        ->pluck('suggestion')
                        ->toArray();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Suggestions retrieved successfully',
                'data' => [
                    'suggestions' => $suggestions,
                    'field' => $field,
                    'query' => $searchQuery
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply field filter based on operator
     *
     * @param $query
     * @param string $field
     * @param string $operator
     * @param string $value
     */
    private function applyFieldFilter($query, string $field, string $operator, string $value): void
    {
        switch ($operator) {
            case 'like':
                $query->where($field, 'LIKE', "%{$value}%");
                break;
            case 'equals':
                $query->where($field, '=', $value);
                break;
            case 'starts_with':
                $query->where($field, 'LIKE', "{$value}%");
                break;
            case 'ends_with':
                $query->where($field, 'LIKE', "%{$value}");
                break;
        }
    }
}
