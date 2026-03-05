<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\User;
use App\Notifications\NewDonationReceived;
use App\Services\ShwaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DonationController extends Controller
{
    protected ShwaryService $shwaryService;

    public function __construct(ShwaryService $shwaryService)
    {
        $this->shwaryService = $shwaryService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $allowedSorts = ['amount', 'currency', 'status', 'created_at', 'completed_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $donations = Donation::query()
            ->with(['user'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('uuid', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('shwary_transaction_id', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Donation $d) => [
                'id' => $d->id,
                'uuid' => $d->uuid,
                'donor_name' => $d->user?->name ?? 'Anonyme',
                'donor_email' => $d->user?->email,
                'recipient_type' => 'platform',
                'recipient_name' => 'Plateforme Mahubiri',
                'amount' => (float) $d->amount,
                'formatted_amount' => $d->formatted_amount,
                'currency' => $d->currency,
                'phone_number' => $d->phone_number,
                'status' => $d->status,
                'is_sandbox' => $d->is_sandbox,
                'message' => $d->message,
                'failure_reason' => $d->failure_reason,
                'completed_at' => $d->completed_at?->format('d/m/Y H:i'),
                'created_at' => $d->created_at->format('d/m/Y H:i'),
                'created_at_human' => $d->created_at->diffForHumans(),
            ]);

        // Summary stats
        $stats = [
            'total' => Donation::count(),
            'completed' => Donation::completed()->count(),
            'pending' => Donation::pending()->count(),
            'failed' => Donation::failed()->count(),
            'total_amount' => number_format(Donation::completed()->sum('amount'), 2) . ' CDF',
        ];

        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the donation creation form (accessible to all admins).
     */
    public function create()
    {
        $countries = $this->shwaryService->getSupportedCountries();
        $defaultCountry = config('shwary.default_country', 'DRC');

        return Inertia::render('Admin/Donations/Create', [
            'countries' => $countries,
            'defaultCountry' => $defaultCountry,
            'isSandbox' => $this->shwaryService->isSandbox(),
        ]);
    }

    /**
     * Process a donation from the admin dashboard.
     * All donations go to the platform (super admin).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $countryCode = $request->input('country_code', config('shwary.default_country', 'DRC'));
        $minAmount = config("shwary.countries.{$countryCode}.min_amount", 100);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', "min:{$minAmount}"],
            'phone_number' => ['required', 'string', 'regex:/^\+\d{10,15}$/'],
            'country_code' => ['sometimes', 'string'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');

        $donation = Donation::create([
            'user_id' => $user->id,
            'church_id' => null,
            'preacher_profile_id' => null,
            'amount' => $validated['amount'],
            'currency' => $currency,
            'country_code' => $countryCode,
            'phone_number' => $validated['phone_number'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'is_sandbox' => $this->shwaryService->isSandbox(),
        ]);

        try {
            $callbackUrl = config('shwary.callback_url');

            $result = $this->shwaryService->initiatePayment(
                (float) $validated['amount'],
                $validated['phone_number'],
                $countryCode,
                $callbackUrl
            );

            if ($result['success']) {
                $data = $result['data'];
                $donation->update([
                    'shwary_transaction_id' => $data['id'] ?? null,
                    'shwary_reference_id' => $data['referenceId'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                ]);

                if (($data['status'] ?? 'pending') === 'completed') {
                    $donation->markAsCompleted($data['id']);
                    $this->notifyAdmins($donation);
                }

                $message = $donation->is_sandbox
                    ? 'Don effectué avec succès (mode test).'
                    : 'Paiement initié. Veuillez valider la transaction sur votre téléphone.';

                return redirect()->route('donations.index')
                    ->with('success', $message);
            }

            $donation->markAsFailed($result['error'] ?? 'Échec du paiement');

            return back()->withErrors([
                'payment' => $result['error'] ?? 'Échec de l\'initiation du paiement.',
            ]);

        } catch (\Exception $e) {
            Log::error('Admin donation payment error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            $donation->markAsFailed('Erreur technique lors du paiement');

            return back()->withErrors([
                'payment' => 'Une erreur est survenue. Veuillez réessayer.',
            ]);
        }
    }

    /**
     * Notify super admins of a completed donation.
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
        } catch (\Exception $e) {
            Log::error('Failed to send donation notification', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
