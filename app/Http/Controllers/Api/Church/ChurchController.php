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
        // Improved query with selective columns, optional search, optional "mine" filter and pagination
        $perPage = (int) request()->query('per_page', 15);
        $search = request()->query('search');
        $onlyMine = filter_var(request()->query('mine', false), FILTER_VALIDATE_BOOLEAN);

        $query = Church::query()
            ->with([
                'createdBy:id,name,email',
                'sermons' => function ($q) {
                    $q->select('id', 'church_id', 'title', 'created_at')
                        ->orderByDesc('created_at');
                }
            ])
            ->select(
                'id',
                'name',
                'abbreviation',
                'slug',
                'address',
                'city',
                'country_name',
                'country_code',
                'logo_url',
                'description',
                'created_by',
                'created_at'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('abbreviation', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($onlyMine && Auth::check()) {
            $query->where('created_by', Auth::id());
        }

        $churches = $query->orderByDesc('created_at')->paginate($perPage);
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
}
