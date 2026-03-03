<?php

namespace App\Http\Controllers\Api\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChurchRequest;
use App\Http\Requests\UpdateChurchRequest;
use App\Http\Resources\ChurchResource;
use App\Models\Church;
use App\Models\User;
use App\Notifications\NewChurchCreated;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChurchController extends Controller
{
    /**
     * Upload service for handling file uploads
     */
    private FileUploadService $uploadService;

    /**
     * Notification service for sending push notifications
     */
    private NotificationService $notificationService;

    /**
     * Create a new controller instance.
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
    public function index(): JsonResponse
    {
        // Improved query with selective columns, optional search, optional "mine" filter and pagination
        $perPage = (int) request()->query('per_page', 15);
        $search = request()->query('search');
        $onlyMine = filter_var(request()->query('mine', false), FILTER_VALIDATE_BOOLEAN);

        $query = Church::query()
            ->active() // Only active churches
            ->with([
                'createdBy:id,name,email',
                'sermons' => function ($q) {
                    $q->where('is_published', true)
                        ->select('id', 'church_id', 'title', 'created_at')
                        ->orderByDesc('created_at');
                }
            ])
            ->withCount(['sermons' => function ($q) {
                $q->where('is_published', true);
            }])
            ->withCount(['sermonViews' => function ($q) {
                $q->where('completed', true);
            }])
            ->withCount(['sermonViews as total_views'])
            ->select(
                'id',
                'name',
                'abbreviation',
                'address',
                'city',
                'country_name',
                'country_code',
                'logo_url',
                'description',
                'created_by',
                'is_featured',
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

        $churches = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate($perPage);
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
        $church->loadCount('sermons')
            ->loadCount(['sermonViews as total_views'])
            ->load('createdBy');

        //Update role of the user to 'church_admin'
        $user = Auth::user();
        $user->role_id = \App\Enums\RoleType::CHURCH_ADMIN;
        $user->save();

        // Send push notification to all users about new church
        try {
            $result = $this->notificationService->sendToAllUsers(
                'new_church',
                [
                    'title' => 'Nouvelle église disponible',
                    'body' => "{$church->name} vient de rejoindre Mahubiri",
                    'data' => [
                        'church_id' => $church->id,
                        'church_name' => $church->name,
                        'type' => 'new_church'
                    ]
                ]
            );

            Log::info('Push notification sent for new church', [
                'church_id' => $church->id,
                'result' => $result
            ]);
        } catch (\Exception $notifException) {
            // Log notification error but don't fail the church creation
            Log::error('Failed to send push notification for new church', [
                'church_id' => $church->id,
                'error' => $notifException->getMessage()
            ]);
        }

        // Notify all admins about new church
        $this->notifyAdmins($church);

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
        $church->loadCount('sermons')
            ->loadCount(['sermonViews' => function ($q) {
                $q->where('completed', true);
            }])
            ->loadCount(['sermonViews as total_views'])
            ->load('createdBy');
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
        $church->loadCount('sermons')
            ->loadCount(['sermonViews as total_views'])
            ->load('createdBy');

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
        // Optionally, you might want to downgrade the user's role here
        $user = Auth::user();
        $user->role_id = \App\Enums\RoleType::USER;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Church deleted successfully'
        ]);
    }

    /**
     * Notify all admins about new church creation
     */
    private function notifyAdmins(Church $church): void
    {
        try {
            // Get all admin users
            $admins = User::whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })->get();

            Log::info('Looking for admins to notify about new church', [
                'church_id' => $church->id,
                'admin_count' => $admins->count(),
                'admin_ids' => $admins->pluck('id')->toArray()
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admins found to notify about new church');
                return;
            }

            // Send notification to each admin
            foreach ($admins as $admin) {
                $admin->notify(new NewChurchCreated($church));
                Log::info('Church notification sent to admin', [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'church_id' => $church->id
                ]);
            }

            Log::info('All admins notified about new church', [
                'church_id' => $church->id,
                'admin_count' => $admins->count()
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail church creation
            Log::error('Failed to notify admins about new church', [
                'church_id' => $church->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
