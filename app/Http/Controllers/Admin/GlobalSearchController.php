<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    /**
     * Perform a global search across sermons, churches, users, etc.
     * Results are scoped to the authenticated user's role.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $query = trim($request->input('q'));
        $user = Auth::user();
        $isAdmin = $user->role_id === RoleType::ADMIN;
        $results = [];

        // ── Sermons ──────────────────────────────────────────────
        $sermonsQuery = Sermon::query()
            ->with('church:id,name')
            ->select('id', 'title', 'preacher_name', 'church_id', 'cover_url', 'is_published', 'created_at');

        // Church admins only see their own church's sermons
        if (!$isAdmin) {
            $churchId = $user->church_id ?? Church::where('created_by', $user->id)->value('id');
            if ($churchId) {
                $sermonsQuery->where('church_id', $churchId);
            }
        }

        $sermons = $sermonsQuery
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('preacher_name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($sermons as $sermon) {
            $results[] = [
                'type' => 'sermon',
                'id' => $sermon->id,
                'title' => $sermon->title,
                'subtitle' => $sermon->preacher_name . ($sermon->church ? ' · ' . $sermon->church->name : ''),
                'url' => '/admin/sermons/' . $sermon->id . '/edit',
                'image' => $sermon->cover_url ? asset($sermon->cover_url) : null,
                'badge' => $sermon->is_published ? 'Publié' : 'Brouillon',
                'badge_color' => $sermon->is_published ? 'emerald' : 'amber',
            ];
        }

        // ── Churches (super admin only) ──────────────────────────
        if ($isAdmin) {
            $churches = Church::query()
                ->select('id', 'name', 'abbreviation', 'city', 'country_name', 'logo_url', 'is_active')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('abbreviation', 'LIKE', "%{$query}%")
                      ->orWhere('visionary_name', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(5)
                ->get();

            foreach ($churches as $church) {
                $results[] = [
                    'type' => 'church',
                    'id' => $church->id,
                    'title' => $church->name,
                    'subtitle' => collect([$church->city, $church->country_name])->filter()->implode(', '),
                    'url' => '/admin/churches?tab=churches&search=' . urlencode($church->name),
                    'image' => $church->logo_url ? asset($church->logo_url) : null,
                    'badge' => $church->is_active ? 'Active' : 'Inactive',
                    'badge_color' => $church->is_active ? 'emerald' : 'red',
                ];
            }

            // ── Independent Preachers (super admin only) ─────────
            $preachers = PreacherProfile::query()
                ->with('user:id,name,email')
                ->select('id', 'user_id', 'ministry_name', 'ministry_type', 'avatar_url', 'city', 'country_name', 'is_active')
                ->where(function ($q) use ($query) {
                    $q->where('ministry_name', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%")
                      ->orWhereHas('user', function ($uq) use ($query) {
                          $uq->where('name', 'LIKE', "%{$query}%")
                              ->orWhere('email', 'LIKE', "%{$query}%");
                      });
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($preachers as $p) {
                $results[] = [
                    'type' => 'preacher',
                    'id' => $p->id,
                    'title' => $p->user?->name ?? $p->ministry_name,
                    'subtitle' => collect([$p->ministry_name, $p->city])->filter()->implode(' · '),
                    'url' => '/admin/churches?tab=preachers&search=' . urlencode($p->user?->name ?? $p->ministry_name),
                    'image' => $p->avatar_url ? asset($p->avatar_url) : null,
                    'badge' => $p->is_active ? 'Active' : 'Inactive',
                    'badge_color' => $p->is_active ? 'emerald' : 'red',
                ];
            }

            // ── Users (super admin only) ─────────────────────────
            $users = User::query()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($users as $u) {
                $results[] = [
                    'type' => 'user',
                    'id' => $u->id,
                    'title' => $u->name,
                    'subtitle' => $u->email,
                    'url' => '/admin/users?search=' . urlencode($u->name),
                    'image' => null,
                    'badge' => ucfirst(str_replace('_', ' ', $u->role ?? 'user')),
                    'badge_color' => 'blue',
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'count' => count($results),
            'query' => $query,
        ]);
    }
}
