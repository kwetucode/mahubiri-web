<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSermonRequest;
use App\Http\Requests\UpdateSermonRequest;
use App\Http\Resources\SermonResource;
use App\Models\Church;
use App\Models\Sermon;
use App\Services\UploadSermonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SermonController extends Controller
{
    /**
     * @var UploadSermonService
     */
    private UploadSermonService $uploadService;

    /**
     * SermonController constructor.
     *
     * @param UploadSermonService $uploadService
     */
    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sermon::with(['church']);
        // Filter by church if provided
        if ($request->has('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        // Search by title or preacher name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('preacher_name', 'like', "%{$search}%");
            });
        }

        $sermons = $query->orderBy('created_at', 'desc')
            ->where('church_id', Auth::user()->church->id)
            ->paginate(15);

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
            // Verify that the church belongs to the user
            if (!$this->verifyChurchOwnership($validated['church_id'])) {
                Log::warning('Unauthorized sermon creation attempt', ['user_id' => Auth::id(), 'church_id' => $validated['church_id']]);
                return $this->errorResponse(
                    'Vous ne pouvez ajouter des sermons qu\'à votre propre église.',
                    ['church' => ['Ajout non autorisé.']],
                    403
                );
            }

            // Handle file uploads
            $this->handleFileUploads($validated);

            $sermon = Sermon::create($validated);
            $sermon->load(['church']);
            Log::info('Sermon created successfully', ['sermon' => $sermon]);
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
        $sermon->load(['church']);
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

        $sermon->update($validated);
        $sermon->load(['church']);

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
     * Verify if user owns the church that contains the sermon
     */
    private function verifySermonOwnership(Sermon $sermon): bool
    {
        return Church::where('id', $sermon->church_id)
            ->where('created_by', Auth::id())
            ->exists();
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
        if (!empty($validated['audio_base64']) || !empty($validated['audio_file'])) {
            // Delete old audio if updating
            if ($existingSermon && $existingSermon->audio_url) {
                $this->uploadService->deleteFile($existingSermon->audio_url, 'audio');
                Log::info('Old audio file deleted', ['audio_url' => $existingSermon->audio_url]);
            }
            // Handle base64 audio
            if (!empty($validated['audio_base64'])) {
                $validated['audio_url'] = $this->uploadService->handleAudioUpload($validated['audio_base64'], 'sermons');
                unset($validated['audio_base64']);
                Log::info('New audio file uploaded', ['audio_url' => $validated['audio_url']]);
            }
            // Handle uploaded audio file
            elseif (!empty($validated['audio_file'])) {
                $validated['audio_url'] = $this->uploadService->handleAudioUpload($validated['audio_file'], 'sermons');
                unset($validated['audio_file']);
                Log::info('New audio file uploaded', ['audio_url' => $validated['audio_url']]);
            }
        }

        // Clean up audio fields if not processed
        unset($validated['audio_base64'], $validated['audio_file']);

        // Handle cover image upload (base64 or file)
        if (!empty($validated['cover_base64']) || !empty($validated['cover_file'])) {
            // Delete old cover if updating
            if ($existingSermon && $existingSermon->cover_url) {
                $this->uploadService->deleteFile($existingSermon->cover_url, 'images');
                Log::info('Old cover image deleted', ['cover_url' => $existingSermon->cover_url]);
            }

            // Handle base64 cover image
            if (!empty($validated['cover_base64'])) {
                $validated['cover_url'] = $this->uploadService->handleImageUpload($validated['cover_base64'], 'covers');
                unset($validated['cover_base64']);
                Log::info('New cover image uploaded', ['cover_url' => $validated['cover_url']]);
            }
            // Handle uploaded cover image file
            elseif (!empty($validated['cover_file'])) {
                $validated['cover_url'] = $this->uploadService->handleImageUpload($validated['cover_file'], 'covers');
                unset($validated['cover_file']);
                Log::info('New cover image uploaded', ['cover_url' => $validated['cover_url']]);
            }
        }
        // Clean up cover fields if not processed
        unset($validated['cover_base64'], $validated['cover_file']);
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
