<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DonationController extends Controller
{
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
            ->with(['user', 'church', 'preacherProfile.user'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('uuid', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('shwary_transaction_id', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('church', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
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
                'recipient_type' => $d->church_id ? 'church' : ($d->preacher_profile_id ? 'preacher' : 'unknown'),
                'recipient_name' => $d->recipient_name,
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
}
