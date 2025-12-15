<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSermonRequest;
use App\Http\Requests\UpdateSermonRequest;
use App\Http\Resources\SermonResource;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Enums\RoleType;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SermonController extends Controller
{
    /**
     * @var FileUploadService
     */
    private FileUploadService $uploadService;

    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;

    /**
     * SermonController Constructor
     * @param FileUploadService $uploadService
     * @param NotificationService $notificationService
     */
    public function __construct(
        FileUploadService $uploadService,
        NotificationService $notificationService
    ) {
        $this->uploadService = $uploadService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sermon::with(['church', 'preacherProfile', 'category']);
        $user = Auth::user();

        // Filter sermons based on user role
        if ($user->role_id === RoleType::CHURCH_ADMIN && $user->church) {
            $query->where('church_id', $user->church->id);
        } elseif ($user->role_id === RoleType::INDEPENDENT_PREACHER && $user->preacherProfile) {
            $query->where('preacher_profile_id', $user->preacherProfile->id);
        }

        // Filter by church if provided
        if ($request->has('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        // Filter by preacher profile if provided
        if ($request->has('preacher_profile_id')) {
            $query->where('preacher_profile_id', $request->preacher_profile_id);
        }

        // Search by title or preacher name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('preacher_name', 'like', "%{$search}%");
            });
        }

        $sermons = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'success' => true,
            'data' => SermonResource::collection($sermons->items()),
            'pagination' => [
                'current_page' => $sermons->currentPage(),
                'last_page' => $sermons->lastPage(),
                'per_page' => $sermons->perPage(),
                'total' => $sermons->total()
            ],
            'message' => 'Sermons retrieved successfully'
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSermonRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $user = Auth::user();

            // Determine if sermon belongs to church or preacher
            if ($user->role_id === RoleType::CHURCH_ADMIN && $user->church) {
                $validated['church_id'] = $user->church->id;
                $validated['preacher_profile_id'] = null;
            } elseif ($user->role_id === RoleType::INDEPENDENT_PREACHER && $user->preacherProfile) {
                $validated['preacher_profile_id'] = $user->preacherProfile->id;
                $validated['church_id'] = null;
            } else {
                return $this->errorResponse(
                    'Vous devez avoir un profil d\'église ou de prédicateur pour publier un sermon.',
                    [],
                    403
                );
            }

            // Handle file uploads
            $this->handleFileUploads($validated);
            Log::info('Creating sermon with data', ['data' => $validated]);
            $sermon = Sermon::create($validated);
            $sermon->load(['church', 'preacherProfile']);
            Log::info('Sermon created successfully', ['sermon' => $sermon]);

            // Send push notification only if sermon is published
            if ($sermon->is_published) {
                try {
                    if ($sermon->church_id) {
                        // Notification for church sermon
                        $this->notificationService->sendToChurch(
                            $sermon->church_id,
                            'new_sermon',
                            [
                                'title' => 'Nouvelle prédication disponible',
                                'body' => "« {$sermon->title} » par {$sermon->preacher_name}",
                                'data' => [
                                    'sermon_id' => $sermon->id,
                                    'church_id' => $sermon->church_id,
                                    'type' => 'new_sermon'
                                ]
                            ]
                        );
                        Log::info('Push notification sent for published church sermon', ['sermon_id' => $sermon->id]);
                    } elseif ($sermon->preacher_profile_id) {
                        // Notification for preacher sermon
                        $this->notificationService->sendToPreacher(
                            $sermon->preacher_profile_id,
                            'new_sermon',
                            [
                                'title' => 'Nouvelle prédication disponible',
                                'body' => "« {$sermon->title} » par {$sermon->preacher_name}",
                                'data' => [
                                    'sermon_id' => $sermon->id,
                                    'preacher_profile_id' => $sermon->preacher_profile_id,
                                    'type' => 'new_sermon'
                                ]
                            ]
                        );
                        Log::info('Push notification sent for published preacher sermon', ['sermon_id' => $sermon->id]);
                    }
                } catch (\Exception $notifException) {
                    // Log notification error but don't fail the sermon creation
                    Log::error('Failed to send push notification for published sermon', [
                        'sermon_id' => $sermon->id,
                        'error' => $notifException->getMessage()
                    ]);
                }
            }

            return $this->successResponse(
                new SermonResource($sermon),
                'Sermon created successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Error creating sermon: ' . $e->getMessage());
            return $this->errorResponse(
                'Erreur lors de la création du sermon.',
                ['exception' => [$e->getMessage()]],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sermon $sermon): JsonResponse
    {
        $sermon->load(['church', 'preacherProfile']);
        return $this->successResponse(
            new SermonResource($sermon),
            'Sermon retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSermonRequest $request, Sermon $sermon): JsonResponse
    {
        // Verify that the sermon belongs to user's church
        if (!$this->verifySermonOwnership($sermon)) {
            return $this->errorResponse(
                'Vous ne pouvez modifier que les sermons de votre église.',
                ['sermon' => ['Modification non autorisée.']],
                403
            );
        }

        $validated = $request->validated();

        // Handle file uploads (with existing sermon for cleanup)
        $this->handleFileUploads($validated, $sermon);
        Log::info('Updating sermon with data', ['sermon_id' => $sermon->id, 'data' => $validated]);
        $sermon->update($validated);
        $sermon->load(['church', 'preacherProfile']);

        return $this->successResponse(
            new SermonResource($sermon),
            'Sermon updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sermon $sermon): JsonResponse
    {
        // Verify that the sermon belongs to user's church
        if (!$this->verifySermonOwnership($sermon)) {
            return $this->errorResponse(
                'Vous ne pouvez supprimer que les sermons de votre église.',
                ['sermon' => ['Suppression non autorisée.']],
                403
            );
        }

        // Delete associated files
        $this->deleteSermonFiles($sermon);

        $sermon->delete();

        return $this->successResponse(
            null,
            'Sermon deleted successfully'
        );
    }

    /**
     * Toggle the publication status of a sermon
     */
    public function togglePublish(Sermon $sermon): JsonResponse
    {
        // Verify that the sermon belongs to user's church
        if (!$this->verifySermonOwnership($sermon)) {
            return $this->errorResponse(
                'Vous ne pouvez modifier que les sermons de votre église.',
                ['sermon' => ['Modification non autorisée.']],
                403
            );
        }

        // Toggle the publication status
        $sermon->is_published = !$sermon->is_published;
        $sermon->save();

        $message = $sermon->is_published
            ? 'Sermon publié avec succès'
            : 'Sermon mis en brouillon';

        // Send push notification only when publishing
        if ($sermon->is_published) {
            try {
                if ($sermon->church_id) {
                    // Notification for church sermon
                    $this->notificationService->sendToChurch(
                        $sermon->church_id,
                        'new_sermon',
                        [
                            'title' => 'Nouvelle prédication disponible',
                            'body' => "« {$sermon->title} » par {$sermon->preacher_name}",
                            'data' => [
                                'sermon_id' => $sermon->id,
                                'church_id' => $sermon->church_id,
                                'type' => 'new_sermon'
                            ]
                        ]
                    );
                    Log::info('Push notification sent for published church sermon', ['sermon_id' => $sermon->id]);
                } elseif ($sermon->preacher_profile_id) {
                    // Notification for preacher sermon
                    $this->notificationService->sendToPreacher(
                        $sermon->preacher_profile_id,
                        'new_sermon',
                        [
                            'title' => 'Nouvelle prédication disponible',
                            'body' => "« {$sermon->title} » par {$sermon->preacher_name}",
                            'data' => [
                                'sermon_id' => $sermon->id,
                                'preacher_profile_id' => $sermon->preacher_profile_id,
                                'type' => 'new_sermon'
                            ]
                        ]
                    );
                    Log::info('Push notification sent for published preacher sermon', ['sermon_id' => $sermon->id]);
                }
            } catch (\Exception $notifException) {
                Log::error('Failed to send push notification for published sermon', [
                    'sermon_id' => $sermon->id,
                    'error' => $notifException->getMessage()
                ]);
            }
        }

        $sermon->load(['church', 'preacherProfile', 'category']);

        return $this->successResponse(
            new SermonResource($sermon),
            $message
        );
    }

    /**
     * Verify if user owns the church or preacher profile that contains the sermon
     */
    private function verifySermonOwnership(Sermon $sermon): bool
    {
        $user = Auth::user();

        // Check if sermon belongs to user's church
        if ($sermon->church_id) {
            return Church::where('id', $sermon->church_id)
                ->where('created_by', $user->id)
                ->exists();
        }

        // Check if sermon belongs to user's preacher profile
        if ($sermon->preacher_profile_id) {
            return PreacherProfile::where('id', $sermon->preacher_profile_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Verify if user owns the specified church
     */
    private function verifyChurchOwnership(int $churchId): bool
    {
        return Church::where('id', $churchId)
            ->where('created_by', Auth::id())
            ->exists();
    }

    /**
     * Handle file uploads for sermon creation/update
     */
    private function handleFileUploads(array &$validated, ?Sermon $existingSermon = null): void
    {
        // Handle audio upload (base64 or file)
        if (!empty($validated['audio'])) {
            // Delete old audio if updating
            if ($existingSermon && $existingSermon->audio_url) {
                $this->uploadService->deleteFile($existingSermon->audio_url, 'audio');
                Log::info('Old audio file deleted', ['audio_url' => $existingSermon->audio_url]);
            }
            // Handle base64 or file audio and extract meta
            $audioMeta = $this->uploadService->handleAudioUploadWithMeta($validated['audio']);
            foreach (
                [
                    'audio_url',
                    'duration',
                    'mime_type',
                    'size',
                    'audio_bitrate',
                    'duration_formatted',
                    'audio_format'
                ] as $field
            ) {
                if (isset($audioMeta[$field])) {
                    $validated[$field] = $audioMeta[$field];
                }
            }
            unset($validated['audio']);
            Log::info('New audio file uploaded', ['audio_url' => $validated['audio_url'], 'meta' => $audioMeta]);
        } elseif (!empty($validated['audio_file'])) {
            $audioMeta = $this->uploadService->handleAudioUploadWithMeta($validated['audio_file']);
            foreach (
                [
                    'audio_url',
                    'duration',
                    'mime_type',
                    'size',
                    'audio_bitrate',
                    'duration_formatted',
                    'audio_format'
                ] as $field
            ) {
                if (isset($audioMeta[$field])) {
                    $validated[$field] = $audioMeta[$field];
                }
            }
            unset($validated['audio_file']);
            Log::info('New audio file uploaded', ['audio_url' => $validated['audio_url'], 'meta' => $audioMeta]);
        }

        // Clean up audio fields if not processed
        unset($validated['audio'], $validated['audio_file']);

        // Handle cover image upload (base64 or file)
        if (!empty($validated['cover'])) {
            // Delete old cover if updating
            if ($existingSermon && $existingSermon->cover_url) {
                $this->uploadService->deleteFile($existingSermon->cover_url, 'images');
                Log::info('Old cover image deleted', ['cover_url' => $existingSermon->cover_url]);
            }

            // Upload the cover image directly (base64 or file)
            $validated['cover_url'] = $this->uploadService->handleImageUpload($validated['cover'], 'covers');
            unset($validated['cover']);
            Log::info('New cover image uploaded', ['cover_url' => $validated['cover_url']]);
        }

        // Clean up cover field if not processed
        unset($validated['cover']);
    }

    /**
     * Delete all files associated with a sermon
     */
    private function deleteSermonFiles(Sermon $sermon): void
    {
        if ($sermon->audio_url) {
            $this->uploadService->deleteFile($sermon->audio_url, 'audio');
        }
        if ($sermon->cover_url) {
            $this->uploadService->deleteFile($sermon->cover_url, 'images');
        }
    }

    /**
     * Return a standardized success response
     */
    private function successResponse($data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    /**
     * Return a standardized error response
     */
    private function errorResponse(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
