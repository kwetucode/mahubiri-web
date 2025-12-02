<?php

namespace App\Http\Controllers\Api\Church;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChurchResource;
use App\Models\Church;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChurchListController extends Controller
{
    /**
     * Display a paginated list of all churches
     */
    public function index(Request $request): JsonResponse
    {
        $query = Church::with(['createdBy'])
            ->withCount('sermons')
            ->withCount(['sermonViews' => function ($q) {
                $q->where('completed', true);
            }]);

        // Search by name or city
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country_name', 'like', "%{$search}%");
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country_name', 'like', '%' . $request->country . '%');
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Order by most recent first
        $churches = $query->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'success' => true,
            'data' => ChurchResource::collection($churches->items()),
            'pagination' => [
                'current_page' => $churches->currentPage(),
                'last_page' => $churches->lastPage(),
                'per_page' => $churches->perPage(),
                'total' => $churches->total(),
                'from' => $churches->firstItem(),
                'to' => $churches->lastItem(),
            ],
            'message' => 'Churches retrieved successfully'
        ]);
    }

    /**
     * Get churches by country with pagination
     */
    public function byCountry(Request $request, string $country): JsonResponse
    {
        $query = Church::with(['createdBy'])
            ->where('country_name', 'like', "%{$country}%")
            ->withCount('sermons')
            ->withCount(['sermonViews' => function ($q) {
                $q->where('completed', true);
            }]);

        // Additional search within country
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $churches = $query->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'success' => true,
            'data' => ChurchResource::collection($churches->items()),
            'pagination' => [
                'current_page' => $churches->currentPage(),
                'last_page' => $churches->lastPage(),
                'per_page' => $churches->perPage(),
                'total' => $churches->total(),
                'from' => $churches->firstItem(),
                'to' => $churches->lastItem(),
            ],
            'message' => "Churches in {$country} retrieved successfully"
        ]);
    }

    /**
     * Get churches by city with pagination
     */
    public function byCity(Request $request, string $city): JsonResponse
    {
        $query = Church::with(['createdBy'])
            ->where('city', 'like', "%{$city}%")
            ->withCount('sermons')
            ->withCount(['sermonViews' => function ($q) {
                $q->where('completed', true);
            }]);

        // Additional search within city
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $churches = $query->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'success' => true,
            'data' => ChurchResource::collection($churches->items()),
            'pagination' => [
                'current_page' => $churches->currentPage(),
                'last_page' => $churches->lastPage(),
                'per_page' => $churches->perPage(),
                'total' => $churches->total(),
                'from' => $churches->firstItem(),
                'to' => $churches->lastItem(),
            ],
            'message' => "Churches in {$city} retrieved successfully"
        ]);
    }
}
