<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorageUpgrade;
use App\Services\StorageUpgradeService;
use App\Services\ShwaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StorageUpgradeController extends Controller
{
    protected StorageUpgradeService $upgradeService;

    public function __construct(StorageUpgradeService $upgradeService)
    {
        $this->upgradeService = $upgradeService;
    }

    /**
     * Show storage upgrade page with plans and history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $church = $user->church;

        if (!$church) {
            abort(403, 'Aucune église associée à votre compte.');
        }

        $countryCode = $request->input('country', config('shwary.default_country', 'DRC'));
        $plans = $this->upgradeService->getPlans($countryCode);

        // Current storage info
        $storageInfo = [
            'current_limit_gb' => round($church->storage_limit / (1024 * 1024 * 1024), 2),
            'used_bytes' => $church->getStorageUsedBytes(),
            'used_gb' => round($church->getStorageUsedBytes() / (1024 * 1024 * 1024), 2),
            'used_mb' => round($church->getStorageUsedBytes() / (1024 * 1024), 2),
            'used_percentage' => $church->getStorageUsedPercentage(),
            'remaining_gb' => round($church->getStorageRemainingBytes() / (1024 * 1024 * 1024), 2),
            'status' => $church->getStorageStatus(),
            'can_upload' => !$church->isStorageQuotaExceeded(),
        ];

        // Purchase history
        $history = StorageUpgrade::where('church_id', $church->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn (StorageUpgrade $u) => [
                'id' => $u->id,
                'uuid' => $u->uuid,
                'extra_gb' => round($u->extra_bytes / (1024 * 1024 * 1024), 0),
                'amount' => (float) $u->amount,
                'formatted_amount' => number_format($u->amount, 2) . ' ' . $u->currency,
                'currency' => $u->currency,
                'status' => $u->status,
                'is_sandbox' => $u->is_sandbox,
                'failure_reason' => $u->failure_reason,
                'completed_at' => $u->completed_at?->format('d/m/Y H:i'),
                'created_at' => $u->created_at->format('d/m/Y H:i'),
                'created_at_human' => $u->created_at->diffForHumans(),
            ]);

        $countries = collect(config('shwary.countries'))->map(fn ($c, $code) => [
            'code' => $code,
            'name' => $c['name'],
            'currency' => $c['currency'],
            'phone_prefix' => $c['phone_prefix'],
        ])->values();

        return Inertia::render('Admin/StorageUpgrade/Index', [
            'plans' => $plans,
            'storageInfo' => $storageInfo,
            'history' => $history,
            'countries' => $countries,
            'selectedCountry' => $countryCode,
            'churchName' => $church->name,
            'isSandbox' => app(ShwaryService::class)->isSandbox(),
        ]);
    }

    /**
     * Purchase a storage upgrade plan.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $church = $user->church;

        if (!$church) {
            abort(403, 'Aucune église associée à votre compte.');
        }

        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:1gb,3gb,5gb'],
            'phone_number' => ['required', 'string', 'regex:/^\+\d{10,15}$/'],
            'country_code' => ['sometimes', 'string'],
        ]);

        $countryCode = $validated['country_code'] ?? config('shwary.default_country', 'DRC');

        $result = $this->upgradeService->purchaseUpgrade(
            $user,
            $church,
            $validated['plan'],
            $validated['phone_number'],
            $countryCode
        );

        if ($result['success']) {
            return redirect()->route('storage-upgrade.index')
                ->with('success', $result['message'])
                ->with('upgrade_uuid', $result['upgrade']->uuid);
        }

        return back()->withErrors([
            'payment' => $result['error'] ?? 'Échec du paiement.',
        ]);
    }

    /**
     * Check upgrade status (AJAX endpoint for polling).
     */
    public function checkStatus(string $uuid)
    {
        $user = Auth::user();
        $church = $user->church;

        if (!$church) {
            return response()->json(['status' => 'error'], 403);
        }

        $upgrade = StorageUpgrade::where('uuid', $uuid)
            ->where('church_id', $church->id)
            ->first();

        if (!$upgrade) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // If still pending and has a Shwary transaction, refresh from Shwary
        if ($upgrade->status === 'pending' && $upgrade->shwary_transaction_id) {
            try {
                $shwary = app(ShwaryService::class);
                $result = $shwary->getTransaction($upgrade->shwary_transaction_id);
                if ($result['success'] && isset($result['data'])) {
                    $newStatus = $result['data']['status'] ?? $upgrade->status;
                    if ($newStatus !== $upgrade->status) {
                        if ($newStatus === 'completed') {
                            $upgrade->markAsCompleted($result['data']['id'] ?? null);
                        } elseif ($newStatus === 'failed') {
                            $upgrade->markAsFailed($result['data']['failureReason'] ?? 'Transaction échouée');
                        } else {
                            $upgrade->update(['status' => $newStatus]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to refresh upgrade status', [
                    'upgrade_id' => $upgrade->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $upgrade->refresh();

        return response()->json([
            'status' => $upgrade->status,
            'failure_reason' => $upgrade->failure_reason,
        ]);
    }
}
