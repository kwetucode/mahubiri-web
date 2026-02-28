<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PreacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminManagementController extends Controller
{
    /**
     * Check if user has admin privileges
     */
    private function checkAdmin()
    {
        $user = Auth::user();
        if (!$user->role || !$user->role->hasAdminPrivileges()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Droits administrateur requis.',
            ], 403);
        }
        return null;
    }

    // ==================== CHURCHES ====================

    /**
     * List all churches with pagination and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listChurches(Request $request)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $query = Church::with(['createdBy:id,name,email'])
                ->withCount(['sermons', 'sermons as published_sermons_count' => function ($q) {
                    $q->where('is_published', true);
                }]);

            // Filter by status
            if ($request->has('status')) {
                $isActive = $request->status === 'active';
                $query->where('is_active', $isActive);
            }

            // Filter by country
            if ($request->filled('country')) {
                $query->where('country_name', 'like', '%' . $request->country . '%');
            }

            // Filter by city
            if ($request->filled('city')) {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            // Search by name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('abbreviation', 'like', '%' . $search . '%')
                      ->orWhere('visionary_name', 'like', '%' . $search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['name', 'created_at', 'country_name', 'city', 'is_active'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $churches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des églises récupérée avec succès',
                'data' => $churches,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get church details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showChurch($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $church = Church::with(['createdBy:id,name,email'])
                ->withCount(['sermons', 'sermons as published_sermons_count' => function ($q) {
                    $q->where('is_published', true);
                }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $church,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Église non trouvée',
            ], 404);
        }
    }

    /**
     * Toggle church active status
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleChurchStatus($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $church = Church::findOrFail($id);
            $church->is_active = !$church->is_active;
            $church->save();

            $status = $church->is_active ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "L'église '{$church->name}' a été {$status} avec succès",
                'data' => [
                    'id' => $church->id,
                    'name' => $church->name,
                    'is_active' => $church->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Église non trouvée',
            ], 404);
        }
    }

    /**
     * Activate a church
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateChurch($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $church = Church::findOrFail($id);

            if ($church->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "L'église '{$church->name}' est déjà active",
                ], 400);
            }

            $church->is_active = true;
            $church->save();

            return response()->json([
                'success' => true,
                'message' => "L'église '{$church->name}' a été activée avec succès",
                'data' => [
                    'id' => $church->id,
                    'name' => $church->name,
                    'is_active' => $church->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Église non trouvée',
            ], 404);
        }
    }

    /**
     * Deactivate a church
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivateChurch($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $church = Church::findOrFail($id);

            if (!$church->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "L'église '{$church->name}' est déjà désactivée",
                ], 400);
            }

            $church->is_active = false;
            $church->save();

            return response()->json([
                'success' => true,
                'message' => "L'église '{$church->name}' a été désactivée avec succès",
                'data' => [
                    'id' => $church->id,
                    'name' => $church->name,
                    'is_active' => $church->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Église non trouvée',
            ], 404);
        }
    }

    /**
     * Bulk update church statuses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateChurchStatus(Request $request)
    {
        if ($error = $this->checkAdmin()) return $error;

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:churches,id',
            'is_active' => 'required|boolean',
        ]);

        try {
            $updated = Church::whereIn('id', $request->ids)
                ->update(['is_active' => $request->is_active]);

            $status = $request->is_active ? 'activées' : 'désactivées';

            return response()->json([
                'success' => true,
                'message' => "{$updated} église(s) {$status} avec succès",
                'data' => [
                    'updated_count' => $updated,
                    'is_active' => $request->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== PREACHER PROFILES ====================

    /**
     * List all preacher profiles with pagination and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPreachers(Request $request)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $query = PreacherProfile::with(['user:id,name,email'])
                ->withCount(['sermons', 'sermons as published_sermons_count' => function ($q) {
                    $q->where('is_published', true);
                }]);

            // Filter by status
            if ($request->has('status')) {
                $isActive = $request->status === 'active';
                $query->where('is_active', $isActive);
            }

            // Filter by ministry type
            if ($request->filled('ministry_type')) {
                $query->where('ministry_type', $request->ministry_type);
            }

            // Filter by country
            if ($request->filled('country')) {
                $query->where('country_name', 'like', '%' . $request->country . '%');
            }

            // Filter by city
            if ($request->filled('city')) {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            // Search by name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('ministry_name', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['ministry_name', 'created_at', 'country_name', 'city', 'is_active', 'ministry_type'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $preachers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des prédicateurs récupérée avec succès',
                'data' => $preachers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get preacher profile details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPreacher($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $preacher = PreacherProfile::with(['user:id,name,email'])
                ->withCount(['sermons', 'sermons as published_sermons_count' => function ($q) {
                    $q->where('is_published', true);
                }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $preacher,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil prédicateur non trouvé',
            ], 404);
        }
    }

    /**
     * Toggle preacher profile active status
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function togglePreacherStatus($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $preacher = PreacherProfile::findOrFail($id);
            $preacher->is_active = !$preacher->is_active;
            $preacher->save();

            $status = $preacher->is_active ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Le profil '{$preacher->ministry_name}' a été {$status} avec succès",
                'data' => [
                    'id' => $preacher->id,
                    'ministry_name' => $preacher->ministry_name,
                    'is_active' => $preacher->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil prédicateur non trouvé',
            ], 404);
        }
    }

    /**
     * Activate a preacher profile
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activatePreacher($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $preacher = PreacherProfile::findOrFail($id);

            if ($preacher->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "Le profil '{$preacher->ministry_name}' est déjà actif",
                ], 400);
            }

            $preacher->is_active = true;
            $preacher->save();

            return response()->json([
                'success' => true,
                'message' => "Le profil '{$preacher->ministry_name}' a été activé avec succès",
                'data' => [
                    'id' => $preacher->id,
                    'ministry_name' => $preacher->ministry_name,
                    'is_active' => $preacher->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil prédicateur non trouvé',
            ], 404);
        }
    }

    /**
     * Deactivate a preacher profile
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivatePreacher($id)
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            $preacher = PreacherProfile::findOrFail($id);

            if (!$preacher->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "Le profil '{$preacher->ministry_name}' est déjà désactivé",
                ], 400);
            }

            $preacher->is_active = false;
            $preacher->save();

            return response()->json([
                'success' => true,
                'message' => "Le profil '{$preacher->ministry_name}' a été désactivé avec succès",
                'data' => [
                    'id' => $preacher->id,
                    'ministry_name' => $preacher->ministry_name,
                    'is_active' => $preacher->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil prédicateur non trouvé',
            ], 404);
        }
    }

    /**
     * Bulk update preacher profile statuses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdatePreacherStatus(Request $request)
    {
        if ($error = $this->checkAdmin()) return $error;

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:preacher_profiles,id',
            'is_active' => 'required|boolean',
        ]);

        try {
            $updated = PreacherProfile::whereIn('id', $request->ids)
                ->update(['is_active' => $request->is_active]);

            $status = $request->is_active ? 'activés' : 'désactivés';

            return response()->json([
                'success' => true,
                'message' => "{$updated} profil(s) prédicateur {$status} avec succès",
                'data' => [
                    'updated_count' => $updated,
                    'is_active' => $request->is_active,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== SUMMARY ====================

    /**
     * Get summary of churches and preachers for admin dashboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary()
    {
        if ($error = $this->checkAdmin()) return $error;

        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'churches' => [
                        'total' => Church::count(),
                        'active' => Church::where('is_active', true)->count(),
                        'inactive' => Church::where('is_active', false)->count(),
                    ],
                    'preachers' => [
                        'total' => PreacherProfile::count(),
                        'active' => PreacherProfile::where('is_active', true)->count(),
                        'inactive' => PreacherProfile::where('is_active', false)->count(),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }
}
