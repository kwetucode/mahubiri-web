<?php

namespace App\Http\Controllers\Api\Preacher;

use App\Enums\MinistryType;
use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePreacherProfileRequest;
use App\Http\Requests\UpdatePreacherProfileRequest;
use App\Http\Resources\PreacherProfileResource;
use App\Models\PreacherProfile;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Services\PreacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PreacherProfileController extends Controller
{
    protected ImageUploadService $imageUploadService;


    public function __construct(
        ImageUploadService $imageUploadService,
        PreacherDashboardService $dashboardService
    ) {
        $this->imageUploadService = $imageUploadService;
    }
    /**
     * Display a listing of preacher profiles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PreacherProfile::with('user');

        // Filter by ministry type
        if ($request->has('ministry_type')) {
            $query->byMinistryType($request->ministry_type);
        }

        // Filter by country
        if ($request->has('country_code')) {
            $query->byCountry($request->country_code);
        }

        // Search by ministry name
        if ($request->has('search')) {
            $query->where('ministry_name', 'LIKE', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $preachers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PreacherProfileResource::collection($preachers),
            'meta' => [
                'current_page' => $preachers->currentPage(),
                'last_page' => $preachers->lastPage(),
                'per_page' => $preachers->perPage(),
                'total' => $preachers->total(),
            ],
        ]);
    }

    /**
     * Store a newly created preacher profile.
     */
    public function store(StorePreacherProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Check if user already has a preacher profile
        if ($user->preacherProfile()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has a preacher profile',
            ], 400);
        }

        // Check if user already has a church
        if ($user->church()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has a church. Cannot create preacher profile.',
            ], 400);
        }

        try {
            // Handle avatar upload if provided
            $avatarUrl = null;
            if ($request->filled('avatar_url')) {
                try {
                    $avatarUrl = $this->imageUploadService->handleImageUpload(
                        $request->avatar_url,
                        'avatars'
                    );
                } catch (\InvalidArgumentException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid avatar image: ' . $e->getMessage(),
                    ], 422);
                }
            }

            $preacherProfile = PreacherProfile::create([
                'user_id' => $user->id,
                'ministry_name' => $request->ministry_name,
                'ministry_type' => $request->ministry_type,
                'avatar_url' => $avatarUrl,
                'country_name' => $request->country_name,
                'country_code' => $request->country_code,
                'city' => $request->city,
                'social_links' => $request->social_links,
            ]);

            // Update user role to INDEPENDENT_PREACHER if not already set
            if ($user->role_id !== RoleType::INDEPENDENT_PREACHER) {
                $user->update(['role_id' => RoleType::INDEPENDENT_PREACHER]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Preacher profile created successfully',
                'data' => new PreacherProfileResource($preacherProfile->load('user')),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating preacher profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create preacher profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified preacher profile.
     */
    public function show(int $id): JsonResponse
    {
        $preacherProfile = PreacherProfile::with(['user', 'sermons' => function ($query) {
            $query->where('is_published', true);
        }])->find($id);

        if (!$preacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Preacher profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PreacherProfileResource($preacherProfile),
        ]);
    }

    /**
     * Update the specified preacher profile.
     */
    public function update(UpdatePreacherProfileRequest $request, int $id): JsonResponse
    {
        $preacherProfile = PreacherProfile::find($id);

        if (!$preacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Preacher profile not found',
            ], 404);
        }

        $user = Auth::user();

        // Check authorization
        if ($preacherProfile->user_id !== $user->id && $user->role_id !== RoleType::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this profile',
            ], 403);
        }

        try {
            // Handle avatar upload if provided
            $updateData = $request->only([
                'ministry_name',
                'ministry_type',
                'country_name',
                'country_code',
                'city',
                'social_links',
            ]);

            if ($request->filled('avatar_url')) {
                try {
                    $updateData['avatar_url'] = $this->imageUploadService->handleImageUpload(
                        $request->avatar_url,
                        'avatars'
                    );
                } catch (\InvalidArgumentException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid avatar image: ' . $e->getMessage(),
                    ], 422);
                }
            }

            $preacherProfile->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Preacher profile updated successfully',
                'data' => new PreacherProfileResource($preacherProfile->load('user')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preacher profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified preacher profile.
     */
    public function destroy(int $id): JsonResponse
    {
        $preacherProfile = PreacherProfile::find($id);
        if (!$preacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Preacher profile not found',
            ], 404);
        }

        try {
            $preacherProfile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Preacher profile deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete preacher profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
