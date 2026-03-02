<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\User;
use App\Notifications\NewDonationReceived;
use App\Services\ShwaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    protected ShwaryService $shwaryService;

    public function __construct(ShwaryService $shwaryService)
    {
        $this->shwaryService = $shwaryService;
    }

    /**
     * Get all donations for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $donations = Donation::where('user_id', $user->id)
            ->with(['church', 'preacherProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => DonationResource::collection($donations),
            'meta' => [
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
            ],
        ]);
    }

    /**
     * Create a new donation and initiate Mobile Money payment.
     *
     * @param StoreDonationRequest $request
     * @return JsonResponse
     */
    public function store(StoreDonationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Récupérer automatiquement l'église ou le profil prédicateur de l'utilisateur connecté
        // (optionnel: un utilisateur sans affiliation peut aussi faire un don)
        $churchId = $user->church?->id;
        $preacherProfileId = $user->preacherProfile?->id;

        $countryCode = $validated['country_code'] ?? config('shwary.default_country', 'DRC');
        $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');

        // Create donation record
        $donation = Donation::create([
            'user_id' => $user->id,
            'church_id' => $churchId,
            'preacher_profile_id' => $preacherProfileId,
            'amount' => $validated['amount'],
            'currency' => $currency,
            'country_code' => $countryCode,
            'phone_number' => $validated['phone_number'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'is_sandbox' => $this->shwaryService->isSandbox(),
        ]);

        Log::info('Donation created', [
            'donation_id' => $donation->id,
            'user_id' => $user->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
        ]);

        // Process payment with Shwary
        try {
            $result = $this->shwaryService->processDonation($donation);

            if ($result['success']) {
                $donation->refresh();
                $this->notifyAdminsOnCompletion($donation, 'pending');

                return response()->json([
                    'success' => true,
                    'message' => $donation->is_sandbox
                        ? 'Don effectué avec succès (mode test).'
                        : 'Paiement initié. Veuillez valider la transaction sur votre téléphone.',
                    'data' => new DonationResource($donation),
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Échec de l\'initiation du paiement.',
                'data' => new DonationResource($donation->fresh()),
            ], 400);

        } catch (\InvalidArgumentException $e) {
            $donation->markAsFailed($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Donation payment error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            $donation->markAsFailed('Erreur technique lors du paiement');

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * Get a specific donation.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $user = Auth::user();

        $donation = Donation::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['church', 'preacherProfile'])
            ->first();

        if (!$donation) {
            return response()->json([
                'success' => false,
                'message' => 'Don non trouvé.',
            ], 404);
        }

        // Refresh status from Shwary if pending
        if ($donation->isPending() && $donation->shwary_transaction_id) {
            $this->refreshDonationStatus($donation);
        }

        return response()->json([
            'success' => true,
            'data' => new DonationResource($donation),
        ]);
    }

    /**
     * Check and refresh donation status from Shwary.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function checkStatus(string $uuid): JsonResponse
    {
        $user = Auth::user();

        $donation = Donation::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$donation) {
            return response()->json([
                'success' => false,
                'message' => 'Don non trouvé.',
            ], 404);
        }

        if (!$donation->shwary_transaction_id) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune transaction Shwary associée.',
            ], 400);
        }

        $oldStatus = $donation->status;
        $shwaryData = $this->refreshDonationStatus($donation);
        $donation->refresh();

        $statusChanged = $oldStatus !== $donation->status;

        return response()->json([
            'success' => true,
            'status_changed' => $statusChanged,
            'message' => $statusChanged
                ? "Statut mis à jour: {$donation->status}"
                : "Statut inchangé: {$donation->status}",
            'data' => new DonationResource($donation->load(['church', 'preacherProfile'])),
            'shwary_response' => $shwaryData, // Pour debug
        ]);
    }

    /**
     * Refresh donation status from Shwary API.
     *
     * @param Donation $donation
     * @return array|null Updated data or null if no update
     */
    protected function refreshDonationStatus(Donation $donation): ?array
    {
        if (!$donation->shwary_transaction_id) {
            return null;
        }

        $result = $this->shwaryService->getTransaction($donation->shwary_transaction_id);

        if (!$result['success'] || !isset($result['data'])) {
            Log::warning('Failed to refresh donation status', [
                'donation_id' => $donation->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return null;
        }

        $data = $result['data'];
        $newStatus = $data['status'] ?? $donation->status;
        $failureReason = $data['failureReason'] ?? $data['failure_reason'] ?? $data['error'] ?? null;

        Log::info('Refreshing donation status', [
            'donation_id' => $donation->id,
            'old_status' => $donation->status,
            'new_status' => $newStatus,
            'failure_reason' => $failureReason,
        ]);

        // Update status if changed
        if ($newStatus !== $donation->status) {
            $oldStatus = $donation->status;

            if ($newStatus === 'completed') {
                $donation->markAsCompleted();
                $this->notifyAdminsOnCompletion($donation, $oldStatus);
            } elseif ($newStatus === 'failed') {
                $donation->markAsFailed($failureReason ?? 'Transaction échouée');
            } else {
                // Update other statuses
                $donation->update(['status' => $newStatus]);
            }
        } elseif ($failureReason && !$donation->failure_reason) {
            // Update failure reason even if status hasn't changed
            $donation->update(['failure_reason' => $failureReason]);
        }

        return $data;
    }

    /**
     * Get supported countries and their configurations.
     *
     * @return JsonResponse
     */
    public function getSupportedCountries(): JsonResponse
    {
        $countries = $this->shwaryService->getSupportedCountries();

        return response()->json([
            'success' => true,
            'data' => $countries,
            'default_country' => config('shwary.default_country', 'DRC'),
            'sandbox_mode' => $this->shwaryService->isSandbox(),
        ]);
    }

    /**
     * Handle Shwary webhook callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCallback(Request $request): JsonResponse
    {
        Log::info('Shwary callback received', $request->all());

        $transactionId = $request->input('id');
        $status = $request->input('status');
        $referenceId = $request->input('referenceId');

        if (!$transactionId) {
            return response()->json(['message' => 'Invalid callback data'], 400);
        }

        // Find donation by Shwary transaction ID or reference ID
        $donation = Donation::where('shwary_transaction_id', $transactionId)
            ->orWhere('shwary_reference_id', $referenceId)
            ->first();

        if (!$donation) {
            Log::warning('Shwary callback: Donation not found', [
                'transaction_id' => $transactionId,
                'reference_id' => $referenceId,
            ]);
            return response()->json(['message' => 'Donation not found'], 404);
        }

        // Update donation status
        $oldStatus = $donation->status;

        if ($status === 'completed') {
            $donation->markAsCompleted($transactionId);
            $this->notifyAdminsOnCompletion($donation, $oldStatus);
            Log::info('Donation completed via callback', ['donation_id' => $donation->id]);
        } elseif ($status === 'failed') {
            $failureReason = $request->input('failureReason') ?? $request->input('error') ?? 'Transaction failed';
            $donation->markAsFailed($failureReason);
            Log::info('Donation failed via callback', [
                'donation_id' => $donation->id,
                'reason' => $failureReason,
            ]);
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }

    /**
     * Notify all admins about a new donation.
     */
    private function notifyAdmins(Donation $donation): void
    {
        try {
            $admins = User::whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewDonationReceived($donation));
            }

            Log::info('Donation notification sent to admins', [
                'donation_id' => $donation->id,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send donation notification', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify admins only when a donation is truly completed.
     */
    private function notifyAdminsOnCompletion(Donation $donation, ?string $previousStatus = null): void
    {
        if (!$donation->isCompleted()) {
            return;
        }

        if ($previousStatus === 'completed') {
            return;
        }

        $this->notifyAdmins($donation);
    }

    /**
     * Get donation statistics for a user.
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_donations' => Donation::where('user_id', $user->id)->completed()->count(),
            'total_amount' => Donation::where('user_id', $user->id)->completed()->sum('amount'),
            'pending_count' => Donation::where('user_id', $user->id)->pending()->count(),
            'failed_count' => Donation::where('user_id', $user->id)->failed()->count(),
            'by_currency' => Donation::where('user_id', $user->id)
                ->completed()
                ->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('currency')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
