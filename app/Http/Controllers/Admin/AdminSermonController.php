<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\CategorySermon;
use App\Models\Sermon;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminSermonController extends Controller
{
    private FileUploadService $uploadService;
    private NotificationService $notificationService;

    public function __construct(FileUploadService $uploadService, NotificationService $notificationService)
    {
        $this->uploadService = $uploadService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check if user is an independent preacher.
     */
    private function isIndependentPreacher($user): bool
    {
        return $user->role_id === RoleType::INDEPENDENT_PREACHER;
    }

    /**
     * Check if user is super admin.
     */
    private function isSuperAdmin($user): bool
    {
        return $user->role_id === RoleType::ADMIN;
    }

    /**
     * Check ownership of a sermon for the current user.
     */
    private function authorizeSermon($user, Sermon $sermon): bool
    {
        // Super admin can manage any sermon
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isIndependentPreacher($user)) {
            $profile = $user->preacherProfile;
            return $profile && $sermon->preacher_profile_id === $profile->id;
        }

        $church = $user->church;
        return $church && $sermon->church_id === $church->id;
    }

    /**
     * List sermons for the authenticated admin's church.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPreacher = $this->isIndependentPreacher($user);
        $isAdmin = $this->isSuperAdmin($user);

        // Determine scope
        if ($isAdmin) {
            $scopeColumn = null;
            $scopeId = null;
            $ownerName = 'Toutes les prédications';
            $ownerId = 0;
        } elseif ($isPreacher) {
            $profile = $user->preacherProfile;
            if (!$profile) {
                abort(403, 'Vous devez avoir un profil de prédicateur pour accéder à cette page.');
            }
            $scopeColumn = 'preacher_profile_id';
            $scopeId = $profile->id;
            $ownerName = $profile->ministry_name;
            $ownerId = $profile->id;
        } else {
            $church = $user->church;
            if (!$church) {
                abort(403, 'Vous devez avoir une église pour accéder à cette page.');
            }
            $scopeColumn = 'church_id';
            $scopeId = $church->id;
            $ownerName = $church->name;
            $ownerId = $church->id;
        }

        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $status = $request->input('status'); // published, draft, all
        $category = $request->input('category'); // category_sermon_id

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $allowedSorts = ['title', 'preacher_name', 'created_at', 'is_published', 'duration'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $sermons = Sermon::query()
            ->when($scopeColumn, fn ($q) => $q->where($scopeColumn, $scopeId))
            ->with('category')
            ->withCount('views')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('preacher_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($status === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($category, fn ($q) => $q->where('category_sermon_id', $category))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Sermon $sermon) => [
                'id' => $sermon->id,
                'title' => $sermon->title,
                'preacher_name' => $sermon->preacher_name,
                'category_name' => $sermon->category?->name,
                'cover_url' => $this->toAbsoluteMediaUrl($sermon->cover_url),
                'audio_url' => $this->toAbsoluteMediaUrl($sermon->audio_url),
                'duration' => $sermon->duration,
                'duration_formatted' => $sermon->duration_formatted,
                'size' => $sermon->size,
                'is_published' => (bool) $sermon->is_published,
                'views_count' => $sermon->views_count,
                'created_at' => $sermon->created_at->format('d/m/Y'),
                'created_at_human' => $sermon->created_at->diffForHumans(),
            ]);

        // Stats
        $statsQuery = Sermon::query()->when($scopeColumn, fn ($q) => $q->where($scopeColumn, $scopeId));
        $totalSermons = (clone $statsQuery)->count();
        $publishedCount = (clone $statsQuery)->where('is_published', true)->count();
        $draftCount = $totalSermons - $publishedCount;
        $totalViews = (clone $statsQuery)->withCount('views')->get()->sum('views_count');

        return Inertia::render('Admin/Sermons/Index', [
            'sermons' => $sermons,
            'categories' => CategorySermon::orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => $totalSermons,
                'published' => $publishedCount,
                'draft' => $draftCount,
                'views' => $totalViews,
            ],
            'church' => [
                'id' => $ownerId,
                'name' => $ownerName,
            ],
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'status' => $status,
                'category' => $category,
            ],
        ]);
    }

    /**
     * Show the form for creating a new sermon.
     */
    public function create()
    {
        $categories = CategorySermon::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Sermons/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created sermon.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isPreacher = $this->isIndependentPreacher($user);
        $isAdmin = $this->isSuperAdmin($user);

        if ($isPreacher) {
            $profile = $user->preacherProfile;
            if (!$profile) {
                abort(403, 'Vous devez avoir un profil de prédicateur.');
            }
        } elseif (!$isAdmin) {
            $church = $user->church;
            if (!$church) {
                abort(403, 'Vous devez avoir une église.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_sermon_id' => 'required|exists:category_sermons,id',
            'preacher_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
            'audio_file' => 'required|file|mimes:mp3,wav,m4a,aac,ogg,flac|max:204800',
            'cover_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'category_sermon_id' => $validated['category_sermon_id'],
                'preacher_name' => $validated['preacher_name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ];

            if ($isPreacher) {
                $data['preacher_profile_id'] = $profile->id;
                $ownerFolder = 'preachers/' . $profile->getStorageFolder();
            } elseif (!$isAdmin) {
                $data['church_id'] = $church->id;
                $ownerFolder = 'churches/' . $church->getStorageFolder();
            } else {
                $ownerFolder = 'admin';
            }

            // Handle audio upload with meta extraction
            if ($request->hasFile('audio_file')) {
                $audioMeta = $this->uploadService->handleAudioUploadWithMeta($request->file('audio_file'), $ownerFolder);
                foreach (['audio_url', 'duration', 'mime_type', 'size', 'audio_bitrate', 'duration_formatted', 'audio_format'] as $field) {
                    if (isset($audioMeta[$field])) {
                        $data[$field] = $audioMeta[$field];
                    }
                }
                Log::info('Admin: Audio uploaded', ['audio_url' => $data['audio_url'] ?? null, 'meta' => $audioMeta]);
            }

            // Handle cover image upload
            if ($request->hasFile('cover_file')) {
                $data['cover_url'] = $this->uploadService->handleImageUpload($request->file('cover_file'), 'covers', $ownerFolder);
                Log::info('Admin: Cover uploaded', ['cover_url' => $data['cover_url']]);
            }

            $sermon = Sermon::create($data);

            Log::info('Admin: Sermon created', ['sermon_id' => $sermon->id]);

            // Send FCM notification if published
            if ($sermon->is_published) {
                $this->sendPublishNotification($sermon);
            }

            return redirect()->route('admin.sermons.index')
                ->with('success', "La prédication « {$sermon->title} » a été créée avec succès.");

        } catch (\Exception $e) {
            Log::error('Admin: Error creating sermon', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Erreur lors de la création de la prédication.'])
                ->withInput();
        }
    }

    /**
     * Show the form for editing a sermon.
     */
    public function edit(Sermon $sermon)
    {
        $user = Auth::user();

        if (!$this->authorizeSermon($user, $sermon)) {
            abort(403, 'Vous ne pouvez modifier que vos prédications.');
        }

        $categories = CategorySermon::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Sermons/Edit', [
            'sermon' => [
                'id' => $sermon->id,
                'title' => $sermon->title,
                'preacher_name' => $sermon->preacher_name,
                'category_sermon_id' => $sermon->category_sermon_id,
                'description' => $sermon->description,
                'color' => $sermon->color,
                'is_published' => (bool) $sermon->is_published,
                'cover_url' => $this->toAbsoluteMediaUrl($sermon->cover_url),
                'audio_url' => $this->toAbsoluteMediaUrl($sermon->audio_url),
                'duration_formatted' => $sermon->duration_formatted,
                'size' => $sermon->size,
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Update an existing sermon.
     */
    public function update(Request $request, Sermon $sermon)
    {
        $user = Auth::user();

        if (!$this->authorizeSermon($user, $sermon)) {
            abort(403, 'Vous ne pouvez modifier que vos prédications.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_sermon_id' => 'required|exists:category_sermons,id',
            'preacher_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,aac,ogg,flac|max:204800',
            'cover_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'category_sermon_id' => $validated['category_sermon_id'],
                'preacher_name' => $validated['preacher_name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ];

            // Determine the owner folder for file storage
            $church = $sermon->church;
            $preacherProfile = $sermon->preacherProfile;
            if ($preacherProfile) {
                $ownerFolder = 'preachers/' . $preacherProfile->getStorageFolder();
            } elseif ($church) {
                $ownerFolder = 'churches/' . $church->getStorageFolder();
            } else {
                $ownerFolder = 'admin';
            }

            // Handle new audio file
            if ($request->hasFile('audio_file')) {
                // Delete old audio
                if ($sermon->audio_url) {
                    $this->uploadService->deleteFile($sermon->audio_url, 'audio');
                    Log::info('Admin: Old audio deleted', ['audio_url' => $sermon->audio_url]);
                }

                $audioMeta = $this->uploadService->handleAudioUploadWithMeta($request->file('audio_file'), $ownerFolder);
                foreach (['audio_url', 'duration', 'mime_type', 'size', 'audio_bitrate', 'duration_formatted', 'audio_format'] as $field) {
                    if (isset($audioMeta[$field])) {
                        $data[$field] = $audioMeta[$field];
                    }
                }
                Log::info('Admin: New audio uploaded', ['audio_url' => $data['audio_url'] ?? null]);
            }

            // Handle new cover image
            if ($request->hasFile('cover_file')) {
                // Delete old cover
                if ($sermon->cover_url) {
                    $this->uploadService->deleteFile($sermon->cover_url, 'images');
                    Log::info('Admin: Old cover deleted', ['cover_url' => $sermon->cover_url]);
                }

                $data['cover_url'] = $this->uploadService->handleImageUpload($request->file('cover_file'), 'covers', $ownerFolder);
                Log::info('Admin: New cover uploaded', ['cover_url' => $data['cover_url']]);
            }

            $sermon->update($data);

            Log::info('Admin: Sermon updated', ['sermon_id' => $sermon->id]);

            return redirect()->route('admin.sermons.index')
                ->with('success', "La prédication « {$sermon->title} » a été mise à jour.");

        } catch (\Exception $e) {
            Log::error('Admin: Error updating sermon', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Erreur lors de la mise à jour de la prédication.'])
                ->withInput();
        }
    }

    /**
     * Toggle the publication status of a sermon.
     */
    public function togglePublish(Sermon $sermon): JsonResponse
    {
        $user = Auth::user();

        if (!$this->authorizeSermon($user, $sermon)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $sermon->update(['is_published' => !$sermon->is_published]);

        // Send FCM notification when publishing
        if ($sermon->is_published) {
            $this->sendPublishNotification($sermon);
        }

        return response()->json([
            'success' => true,
            'is_published' => $sermon->is_published,
            'message' => $sermon->is_published
                ? "La prédication « {$sermon->title} » a été publiée."
                : "La prédication « {$sermon->title} » a été mise en brouillon.",
        ]);
    }

    /**
     * Delete a sermon.
     */
    public function destroy(Sermon $sermon): JsonResponse
    {
        $user = Auth::user();

        if (!$this->authorizeSermon($user, $sermon)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        // Delete associated files
        if ($sermon->audio_url) {
            $this->uploadService->deleteFile($sermon->audio_url, 'audio');
        }
        if ($sermon->cover_url) {
            $this->uploadService->deleteFile($sermon->cover_url, 'images');
        }

        $title = $sermon->title;
        $sermon->delete();

        return response()->json([
            'success' => true,
            'message' => "La prédication « {$title} » a été supprimée.",
        ]);
    }

    private function toAbsoluteMediaUrl(?string $mediaUrl): ?string
    {
        if (!$mediaUrl) {
            return null;
        }

        if (Str::startsWith($mediaUrl, ['http://', 'https://', '//'])) {
            return $mediaUrl;
        }

        return url('/' . ltrim($mediaUrl, '/'));
    }

    /**
     * Send FCM push notification when a sermon is published.
     */
    private function sendPublishNotification(Sermon $sermon): void
    {
        try {
            if ($sermon->church_id) {
                $this->notificationService->sendToChurch(
                    $sermon->church_id,
                    'new_sermon',
                    [
                        'title' => 'Nouvelle prédication disponible',
                        'body' => "« {$sermon->title} » par {$sermon->preacher_name}",
                        'data' => [
                            'sermon_id' => $sermon->id,
                            'church_id' => $sermon->church_id,
                            'type' => 'new_sermon',
                        ],
                    ]
                );
                Log::info('Admin: Push notification sent for published sermon', ['sermon_id' => $sermon->id]);
            }
        } catch (\Exception $e) {
            Log::error('Admin: Failed to send push notification', [
                'sermon_id' => $sermon->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
