<?php

namespace App\Services;

use App\Models\Church;
use App\Models\StorageUpgrade;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StorageUpgradeService
{
    protected ShwaryService $shwaryService;

    public function __construct(ShwaryService $shwaryService)
    {
        $this->shwaryService = $shwaryService;
    }

    /**
     * Get available storage upgrade plans for a country.
     */
    public function getPlans(string $countryCode = 'DRC'): array
    {
        $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');
        $plans = StorageUpgrade::getAvailablePlans();

        return collect($plans)->map(function ($plan, $key) use ($currency) {
            return [
                'key' => $key,
                'label' => $plan['label'],
                'extra_gb' => round($plan['extra_bytes'] / (1024 * 1024 * 1024), 0),
                'extra_bytes' => $plan['extra_bytes'],
                'price' => $plan['prices'][$currency] ?? $plan['prices']['CDF'],
                'currency' => $currency,
            ];
        })->values()->all();
    }

    /**
     * Initiate a storage upgrade purchase via MobileMoney.
     */
    public function purchaseUpgrade(
        User $user,
        Church $church,
        string $planKey,
        string $phoneNumber,
        string $countryCode = 'DRC'
    ): array {
        $plan = StorageUpgrade::getPlan($planKey);

        if (!$plan) {
            return [
                'success' => false,
                'error' => 'Plan de stockage invalide.',
            ];
        }

        $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');
        $price = $plan['prices'][$currency] ?? $plan['prices']['CDF'];

        // Create the upgrade record
        $upgrade = StorageUpgrade::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'extra_bytes' => $plan['extra_bytes'],
            'amount' => $price,
            'currency' => $currency,
            'country_code' => $countryCode,
            'phone_number' => $phoneNumber,
            'status' => 'pending',
            'is_sandbox' => $this->shwaryService->isSandbox(),
        ]);

        Log::info('Storage upgrade created', [
            'upgrade_id' => $upgrade->id,
            'church_id' => $church->id,
            'plan' => $planKey,
            'amount' => $price,
            'currency' => $currency,
        ]);

        // Initiate payment via Shwary
        try {
            $callbackUrl = route('api.v1.storage-upgrades.callback');

            $result = $this->shwaryService->initiatePayment(
                (float) $price,
                $phoneNumber,
                $countryCode,
                $callbackUrl
            );

            if ($result['success']) {
                $data = $result['data'];

                $upgrade->update([
                    'shwary_transaction_id' => $data['id'] ?? null,
                    'shwary_reference_id' => $data['referenceId'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                ]);

                // If sandbox/test mode, transaction completes immediately
                if (($data['status'] ?? 'pending') === 'completed') {
                    $upgrade->markAsCompleted($data['id']);
                }

                return [
                    'success' => true,
                    'upgrade' => $upgrade->fresh(),
                    'message' => $upgrade->is_sandbox
                        ? 'Mise à jour du stockage effectuée avec succès (mode test).'
                        : 'Paiement initié. Veuillez valider la transaction sur votre téléphone.',
                ];
            }

            $upgrade->markAsFailed($result['error'] ?? 'Échec du paiement');

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Échec de l\'initiation du paiement.',
                'upgrade' => $upgrade->fresh(),
            ];

        } catch (\InvalidArgumentException $e) {
            $upgrade->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'upgrade' => $upgrade->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('Storage upgrade payment error', [
                'upgrade_id' => $upgrade->id,
                'error' => $e->getMessage(),
            ]);

            $upgrade->markAsFailed('Erreur interne lors du paiement');

            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors du paiement. Veuillez réessayer.',
                'upgrade' => $upgrade->fresh(),
            ];
        }
    }

    /**
     * Check the status of a storage upgrade payment.
     */
    public function checkStatus(StorageUpgrade $upgrade): array
    {
        if ($upgrade->status === 'completed') {
            return [
                'success' => true,
                'status' => 'completed',
                'message' => 'Votre quota de stockage a été mis à jour avec succès.',
                'upgrade' => $upgrade,
            ];
        }

        if ($upgrade->status === 'failed') {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $upgrade->failure_reason ?? 'Le paiement a échoué.',
                'upgrade' => $upgrade,
            ];
        }

        // Check with Shwary for pending transactions
        if (!$upgrade->shwary_transaction_id) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Transaction en attente.',
                'upgrade' => $upgrade,
            ];
        }

        $result = $this->shwaryService->getTransaction($upgrade->shwary_transaction_id);

        if ($result['success']) {
            $data = $result['data'];
            $status = $data['status'] ?? 'pending';

            if ($status === 'completed') {
                $upgrade->markAsCompleted($upgrade->shwary_transaction_id);

                return [
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Votre quota de stockage a été mis à jour avec succès.',
                    'upgrade' => $upgrade->fresh(),
                ];
            }

            if (in_array($status, ['failed', 'cancelled', 'expired'])) {
                $reason = $data['failureReason'] ?? 'Paiement échoué ou annulé';
                $upgrade->markAsFailed($reason);

                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => $reason,
                    'upgrade' => $upgrade->fresh(),
                ];
            }
        }

        return [
            'success' => false,
            'status' => 'pending',
            'message' => 'Transaction en cours de traitement.',
            'upgrade' => $upgrade,
        ];
    }

    /**
     * Handle callback from Shwary for a storage upgrade.
     */
    public function handleCallback(array $data): bool
    {
        $transactionId = $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$transactionId) {
            Log::warning('Storage upgrade callback: missing transaction ID', $data);
            return false;
        }

        $upgrade = StorageUpgrade::where('shwary_transaction_id', $transactionId)->first();

        if (!$upgrade) {
            Log::warning('Storage upgrade callback: upgrade not found', [
                'transaction_id' => $transactionId,
            ]);
            return false;
        }

        if ($upgrade->status === 'completed') {
            Log::info('Storage upgrade callback: already completed', [
                'upgrade_id' => $upgrade->id,
            ]);
            return true;
        }

        if ($status === 'completed') {
            $upgrade->markAsCompleted($transactionId);

            Log::info('Storage upgrade completed via callback', [
                'upgrade_id' => $upgrade->id,
                'church_id' => $upgrade->church_id,
                'extra_bytes' => $upgrade->extra_bytes,
            ]);

            return true;
        }

        if (in_array($status, ['failed', 'cancelled', 'expired'])) {
            $reason = $data['failureReason'] ?? 'Paiement échoué';
            $upgrade->markAsFailed($reason);

            Log::info('Storage upgrade failed via callback', [
                'upgrade_id' => $upgrade->id,
                'reason' => $reason,
            ]);

            return true;
        }

        return false;
    }
}
