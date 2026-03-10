<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PreacherProfileController extends Controller
{
    /**
     * Show the preacher profile page for the authenticated INDEPENDENT_PREACHER.
     */
    public function show()
    {
        $user = Auth::user();
        $preacher = $user->preacherProfile;

        abort_unless($preacher, 403, 'Aucun profil prédicateur associé à votre compte.');

        $totalSermons = $preacher->sermons()->count();
        $publishedSermons = $preacher->sermons()->where('is_published', true)->count();
        $draftSermons = $totalSermons - $publishedSermons;
        $totalViews = $preacher->sermonViews()->count();
        $totalFavorites = \App\Models\SermonFavorite::whereIn('sermon_id', $preacher->sermons()->select('id'))->count();
        $sermonsThisMonth = $preacher->sermons()->where('created_at', '>=', now()->startOfMonth())->count();
        $viewsThisMonth = $preacher->sermonViews()->where('sermon_views.created_at', '>=', now()->startOfMonth())->count();

        // Top 3 most viewed sermons
        $topSermons = $preacher->sermons()
            ->where('is_published', true)
            ->withCount('views')
            ->orderByDesc('views_count')
            ->take(3)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'preacher_name' => $s->preacher_name,
                'views_count' => $s->views_count,
            ]);

        // Latest sermon date
        $latestSermon = $preacher->sermons()->latest()->first();

        return Inertia::render('Admin/PreacherProfile', [
            'preacher' => [
                'id'                       => $preacher->id,
                'ministry_name'            => $preacher->ministry_name,
                'ministry_type'            => $preacher->ministry_type,
                'ministry_type_description' => $preacher->ministry_type_description,
                'avatar_url'               => $preacher->avatar_url ? $this->toAbsoluteUrl($preacher->avatar_url) : null,
                'country_name'             => $preacher->country_name,
                'country_code'             => $preacher->country_code,
                'city'                     => $preacher->city,
                'full_location'            => $preacher->full_location,
                'social_links'             => $preacher->social_links,
                'is_active'                => $preacher->is_active,
                'sermons_count'            => $totalSermons,
                'created_at'               => $preacher->created_at?->format('d/m/Y'),
                'user_name'                => $user->name,
            ],
            'stats' => [
                'totalSermons'     => $totalSermons,
                'publishedSermons' => $publishedSermons,
                'draftSermons'     => $draftSermons,
                'totalViews'       => $totalViews,
                'totalFavorites'   => $totalFavorites,
                'sermonsThisMonth' => $sermonsThisMonth,
                'viewsThisMonth'   => $viewsThisMonth,
                'publicationRate'  => $totalSermons > 0 ? round(($publishedSermons / $totalSermons) * 100) : 0,
                'lastSermonDate'   => $latestSermon?->created_at?->diffForHumans(),
            ],
            'topSermons' => $topSermons,
        ]);
    }

    /**
     * Update the preacher profile.
     */
    public function update(Request $request, FileUploadService $fileUploadService)
    {
        $user = Auth::user();
        $preacher = $user->preacherProfile;

        abort_unless($preacher, 403, 'Aucun profil prédicateur associé à votre compte.');

        $validated = $request->validate([
            'ministry_name' => ['required', 'string', 'max:255'],
            'ministry_type' => ['nullable', 'string', 'max:50'],
            'country_name'  => ['nullable', 'string', 'max:100'],
            'city'          => ['nullable', 'string', 'max:100'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($preacher->avatar_url) {
                $fileUploadService->deleteFile($preacher->avatar_url, 'image');
            }
            $ownerFolder = 'preachers/' . $preacher->getStorageFolder();
            $validated['avatar_url'] = $fileUploadService->handleImageUpload($request->file('avatar'), 'avatars', $ownerFolder);
        } elseif ($request->boolean('remove_avatar') && $preacher->avatar_url) {
            $fileUploadService->deleteFile($preacher->avatar_url, 'image');
            $validated['avatar_url'] = null;
        }

        // Remove non-model fields
        unset($validated['avatar'], $validated['remove_avatar']);

        $preacher->update($validated);

        return redirect()->route('admin.preacher-profile')
            ->with('success', 'Informations du profil prédicateur mises à jour avec succès.');
    }

    /**
     * Convert a relative storage URL to an absolute URL.
     */
    private function toAbsoluteUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http')) return $url;
        return url($url);
    }
}
