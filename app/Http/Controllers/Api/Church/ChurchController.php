<?php

namespace App\Http\Controllers\Api\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChurchRequest;
use App\Http\Requests\UpdateChurchRequest;
use App\Http\Resources\ChurchResource;
use App\Models\Church;
use App\Services\UploadSermonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChurchController extends Controller
{
    /**
     * Upload service for handling file uploads
     */
    private UploadSermonService $uploadService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $churches = Church::with('createdBy', 'sermons')->get();
        return response()->json([
            'success' => true,
            'data' => ChurchResource::collection($churches),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChurchRequest $request): JsonResponse
    {
        // Vérification supplémentaire de sécurité
        $existingChurch = Church::where('created_by', Auth::id())->first();
        if ($existingChurch) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà créé une église. Un utilisateur ne peut créer qu\'une seule église.',
                'errors' => [
                    'church' => ['Vous avez déjà une église existante.']
                ]
            ], 422);
        }

        $validated = $request->validated();

        // Handle logo upload if present
        if (!empty($validated['logo'])) {
            $validated['logo_url'] = $this->uploadService->handleImageUpload($validated['logo'], 'church_logos');
            unset($validated['logo']);
        }
        // Add the authenticated user as creator
        $validated['created_by'] = Auth::id();

        $church = Church::create($validated);
        $church->load('createdBy');

        return response()->json([
            'success' => true,
            'data' => new ChurchResource($church),
            'message' => 'Church created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Church $church): JsonResponse
    {
        $church->load('createdBy');

        return response()->json([
            'success' => true,
            'data' => new ChurchResource($church),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChurchRequest $request, Church $church): JsonResponse
    {
        $validated = $request->validated();

        // Handle logo upload if present
        if (!empty($validated['logo'])) {
            // Delete old logo if exists
            if ($church->logo_url) {
                $this->uploadService->deleteFile($church->logo_url);
            }
            $validated['logo_url'] = $this->uploadService->handleImageUpload($validated['logo'], 'church_logos');
            unset($validated['logo']);
        }

        $church->update($validated);
        $church->load('createdBy');

        return response()->json([
            'success' => true,
            'data' => new ChurchResource($church),
            'message' => 'Church updated successfully',
            'form' => $validated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Church $church): JsonResponse
    {
        // Delete associated logo if exists
        if ($church->logo_url) {
            $this->uploadService->deleteFile($church->logo_url);
        }

        $church->delete();

        return response()->json([
            'success' => true,
            'message' => 'Church deleted successfully'
        ]);
    }

    /**
     * Check if the authenticated user has already created a church
     */
    public function checkUserChurch(): JsonResponse
    {
        $church = Church::where('created_by', Auth::id())->with('createdBy')->first();

        if ($church) {
            return response()->json([
                'success' => true,
                'has_church' => true,
                'data' => new ChurchResource($church),
                'message' => 'Utilisateur a déjà une église'
            ]);
        }

        return response()->json([
            'success' => true,
            'has_church' => false,
            'data' => null,
            'message' => 'Utilisateur n\'a pas encore d\'église'
        ]);
    }

    /**
     * Update church logo
     */
    public function updateLogo(Request $request, Church $church): JsonResponse
    {
        $request->validate([
            'logo' => 'required|string', // Base64 image
        ]);

        try {
            // Delete old logo if exists
            if ($church->logo_url) {
                $this->uploadService->deleteFile($church->logo_url);
            }

            // Upload new logo
            $logoUrl = $this->uploadService->handleImageUpload($request->logo, 'church_logos');

            // Update church with new logo URL
            $church->update(['logo_url' => $logoUrl]);
            $church->load('createdBy');

            return response()->json([
                'success' => true,
                'data' => new ChurchResource($church),
                'message' => 'Church logo updated successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logo upload failed: ' . $e->getMessage()
            ], 400);
        }
    }
    /**
     * Remove church logo
     */
    public function removeLogo(Church $church): JsonResponse
    {
        if ($church->logo_url) {
            $this->uploadService->deleteFile($church->logo_url);
            $church->update(['logo_url' => null]);
        }

        $church->load('createdBy');

        return response()->json([
            'success' => true,
            'data' => new ChurchResource($church),
            'message' => 'Church logo removed successfully'
        ]);
    }
}
