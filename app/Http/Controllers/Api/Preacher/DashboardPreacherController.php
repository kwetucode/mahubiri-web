<?php

namespace App\Http\Controllers\Api\Preacher;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\PreacherProfile;
use App\Services\PreacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardPreacherController extends Controller
{
    protected PreacherDashboardService $dashboardService;

    public function __construct(
        PreacherDashboardService $dashboardService
    ) {
        $this->dashboardService = $dashboardService;
    }

    /**
     *
     * Get dashboard statistics for authenticated preacher
     */
    public function dashboard(): JsonResponse
    {
        $user = Auth::user();
        $preacherProfile = $user->preacherProfile;

        if (!$preacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'No preacher profile found for this user',
            ], 404);
        }

        try {
            $stats = $this->dashboardService->getDashboardStats($preacherProfile->id);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching preacher dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for a specific preacher (Admin only)
     */
    public function dashboardById(int $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role_id !== RoleType::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can view other preachers\' dashboards.',
            ], 403);
        }

        $preacherProfile = PreacherProfile::active()->find($id);

        if (!$preacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Preacher profile not found',
            ], 404);
        }

        try {
            $stats = $this->dashboardService->getDashboardStats($id);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching preacher dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
