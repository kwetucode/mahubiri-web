<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Enums\MinistryType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ChurchController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'churches');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $churches = null;
        $preachers = null;

        if ($tab === 'churches') {
            $allowedSorts = ['name', 'city', 'country_name', 'created_at', 'is_active'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }

            $churches = Church::query()
                ->with('createdBy')
                ->withCount('sermons')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('abbreviation', 'like', "%{$search}%")
                          ->orWhere('visionary_name', 'like', "%{$search}%")
                          ->orWhere('city', 'like', "%{$search}%")
                          ->orWhere('country_name', 'like', "%{$search}%");
                    });
                })
                ->orderBy($sortBy, $sortDirection)
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (Church $church) => [
                    'id' => $church->id,
                    'name' => $church->name,
                    'abbreviation' => $church->abbreviation,
                    'visionary_name' => $church->visionary_name,
                    'logo_url' => $church->logo_url,
                    'city' => $church->city,
                    'country_name' => $church->country_name,
                    'sermons_count' => $church->sermons_count,
                    'is_active' => $church->is_active,
                    'created_by_name' => $church->createdBy?->name,
                    'created_at' => $church->created_at->format('d/m/Y'),
                    'created_at_human' => $church->created_at->diffForHumans(),
                ]);
        } else {
            $allowedSorts = ['ministry_name', 'ministry_type', 'city', 'country_name', 'created_at', 'is_active'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }

            $preachers = PreacherProfile::query()
                ->with('user')
                ->withCount('sermons')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('ministry_name', 'like', "%{$search}%")
                          ->orWhere('city', 'like', "%{$search}%")
                          ->orWhere('country_name', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($uq) use ($search) {
                              $uq->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                          });
                    });
                })
                ->orderBy($sortBy, $sortDirection)
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (PreacherProfile $p) => [
                    'id' => $p->id,
                    'user_name' => $p->user?->name,
                    'user_email' => $p->user?->email,
                    'ministry_name' => $p->ministry_name,
                    'ministry_type' => $p->ministry_type,
                    'ministry_type_label' => MinistryType::getDescription($p->ministry_type),
                    'avatar_url' => $p->avatar_url,
                    'city' => $p->city,
                    'country_name' => $p->country_name,
                    'sermons_count' => $p->sermons_count,
                    'is_active' => $p->is_active,
                    'created_at' => $p->created_at->format('d/m/Y'),
                    'created_at_human' => $p->created_at->diffForHumans(),
                ]);
        }

        return Inertia::render('Admin/Churches/Index', [
            'churches' => $churches,
            'preachers' => $preachers,
            'filters' => [
                'tab' => $tab,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Toggle the active status of a church.
     */
    public function toggleActive(Church $church): JsonResponse
    {
        $church->update([
            'is_active' => !$church->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $church->is_active,
            'message' => $church->is_active
                ? "L'église \"{$church->name}\" a été activée."
                : "L'église \"{$church->name}\" a été désactivée.",
        ]);
    }

    /**
     * Toggle the active status of a preacher profile.
     */
    public function togglePreacherActive(PreacherProfile $preacher): JsonResponse
    {
        $preacher->update([
            'is_active' => !$preacher->is_active,
        ]);

        $name = $preacher->user?->name ?? $preacher->ministry_name;

        return response()->json([
            'success' => true,
            'is_active' => $preacher->is_active,
            'message' => $preacher->is_active
                ? "Le prédicateur \"{$name}\" a été activé."
                : "Le prédicateur \"{$name}\" a été désactivé.",
        ]);
    }
}
