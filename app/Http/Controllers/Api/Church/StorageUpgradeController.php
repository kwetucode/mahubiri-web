<?php

namespace App\Http\Controllers\Api\Church;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\StorageUpgrade;
use App\Services\StorageUpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StorageUpgradeController extends Controller
{
    protected StorageUpgradeService $upgradeService;

    public function __construct(StorageUpgradeService $upgradeService)
    {
        $this->upgradeService = $upgradeService;
    }

    /**
     * Get available storage upgrade plans.
     *
     * @group Storage Upgrades
     * @authenticated
     */
    public function plans(Request $request): JsonResponse
    {
        $user = Auth::user();
        $church = Church::where('created_by', $user->id)->first();

        if (!$church) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
            ], 404);
        }

        $countryCode = $request->get('country_code', $church->country_code ?? config('shwary.default_country', 'DRC'));
        // Convertir le code ISO (CD) en code Shwary (DRC) si nécessaire
        $apiCountryCode = config("shwary.countries.{$countryCode}.api_code", $countryCode);

        $plans = $this->upgradeService->getPlans($apiCountryCode);

        return response()->json([
            'success' => true,
            'message' => 'Plans de stockage disponibles.',
            'data' => [
                'plans' => $plans,
                'current_storage' => $church->getStorageQuotaSummary(),
            ],
        ]);
    }

    /**
     * Purchase a storage upgrade via MobileMoney.
     *
     * @group Storage Upgrades
     * @authenticated
     */
    public function purchase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan' => ['required', 'string', 'in:1gb,3gb,5gb'],
            'phone_number' => ['required', 'string', 'min:10'],
            'country_code' => ['nullable', 'string'],
        ], [
            'plan.required' => 'Le plan de stockage est requis.',
            'plan.in' => 'Plan de stockage invalide. Options: 1gb, 3gb, 5gb.',
            'phone_number.required' => 'Le numéro de téléphone est requis.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $church = Church::where('created_by', $user->id)->first();

        if (!$church) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
            ], 404);
        }

        $countryCode = $request->get('country_code', $church->country_code ?? config('shwary.default_country', 'DRC'));
        // Convertir le code ISO (CD) en code Shwary (DRC) si nécessaire
        $apiCountryCode = config("shwary.countries.{$countryCode}.api_code", $countryCode);

        $result = $this->upgradeService->purchaseUpgrade(
            $user,
            $church,
            $request->input('plan'),
            $request->input('phone_number'),
            $apiCountryCode
        );

        if ($result['success']) {
            $upgrade = $result['upgrade'];

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'uuid' => $upgrade->uuid,
                    'plan' => $request->input('plan'),
                    'amount' => $upgrade->amount,
                    'currency' => $upgrade->currency,
                    'status' => $upgrade->status,
                    'is_sandbox' => $upgrade->is_sandbox,
                    'extra_gb' => round($upgrade->extra_bytes / (1024 * 1024 * 1024), 0),
                    'current_storage' => $church->fresh()->getStorageQuotaSummary(),
                ],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Échec de la mise à jour du stockage.',
            'data' => isset($result['upgrade']) ? [
                'uuid' => $result['upgrade']->uuid,
                'status' => $result['upgrade']->status,
            ] : null,
        ], 400);
    }

    /**
     * Check the status of a storage upgrade payment.
     *
     * @group Storage Upgrades
     * @authenticated
     */
    public function checkStatus(string $uuid): JsonResponse
    {
        $user = Auth::user();
        $upgrade = StorageUpgrade::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$upgrade) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée.',
            ], 404);
        }

        $result = $this->upgradeService->checkStatus($upgrade);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => [
                'uuid' => $upgrade->uuid,
                'status' => $result['status'],
                'amount' => $upgrade->amount,
                'currency' => $upgrade->currency,
                'extra_gb' => round($upgrade->extra_bytes / (1024 * 1024 * 1024), 0),
                'is_applied' => $upgrade->is_applied,
                'current_storage' => $upgrade->church->getStorageQuotaSummary(),
            ],
        ]);
    }

    /**
     * Get the purchase history for the authenticated user's church.
     *
     * @group Storage Upgrades
     * @authenticated
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        $church = Church::where('created_by', $user->id)->first();

        if (!$church) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas d\'église associée à votre compte.',
            ], 404);
        }

        $upgrades = StorageUpgrade::where('church_id', $church->id)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $upgrades->map(fn (StorageUpgrade $u) => [
                'uuid' => $u->uuid,
                'plan_gb' => round($u->extra_bytes / (1024 * 1024 * 1024), 0),
                'amount' => $u->amount,
                'currency' => $u->currency,
                'status' => $u->status,
                'is_applied' => $u->is_applied,
                'failure_reason' => $u->failure_reason,
                'completed_at' => $u->completed_at?->toISOString(),
                'created_at' => $u->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $upgrades->currentPage(),
                'last_page' => $upgrades->lastPage(),
                'per_page' => $upgrades->perPage(),
                'total' => $upgrades->total(),
            ],
            'current_storage' => $church->getStorageQuotaSummary(),
        ]);
    }

    /**
     * Handle Shwary callback webhook for storage upgrades.
     * Public route — no auth required.
     *
     * @group Storage Upgrades
     */
    public function handleCallback(Request $request): JsonResponse
    {
        Log::info('Storage upgrade callback received', $request->all());

        $handled = $this->upgradeService->handleCallback($request->all());

        return response()->json([
            'success' => $handled,
            'message' => $handled ? 'Callback traité avec succès.' : 'Callback non traité.',
        ]);
    }
}
