<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Sermon;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'usersToday' => User::whereDate('created_at', today())->count(),
                'usersThisWeek' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'usersThisMonth' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }

    /**
     * Return chart data for a given model, grouped by day.
     */
    public function chartData(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,churches,sermons',
            'filter' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $type = $request->input('type');
        $filter = $request->input('filter', 'this_month');

        // Determine date range
        [$startDate, $endDate] = $this->resolveDateRange($filter, $request);

        // Get the appropriate model
        $query = match ($type) {
            'users' => User::query(),
            'churches' => Church::query(),
            'sermons' => Sermon::query(),
        };

        // Group by date
        $counts = $query
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Fill all days in range (including zeros)
        $labels = [];
        $data = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $day) {
            $dateKey = $day->format('Y-m-d');
            $labels[] = $day->format('d M');
            $data[] = $counts[$dateKey] ?? 0;
        }

        $total = array_sum($data);

        // Calculate trend vs previous period
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStart = $startDate->copy()->subDays($periodDays);
        $prevEnd = $startDate->copy()->subDay();

        $prevTotal = match ($type) {
            'users' => User::whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()])->count(),
            'churches' => Church::whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()])->count(),
            'sermons' => Sermon::whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()])->count(),
        };

        $trendPercent = $prevTotal > 0
            ? round((($total - $prevTotal) / $prevTotal) * 100)
            : ($total > 0 ? 100 : 0);

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'total' => $total,
            'trend' => ($trendPercent >= 0 ? '+' : '') . $trendPercent . '%',
            'trendUp' => $trendPercent >= 0,
        ]);
    }

    /**
     * Resolve date range from filter value.
     */
    private function resolveDateRange(string $filter, Request $request): array
    {
        return match ($filter) {
            'this_week' => [now()->startOfWeek(), now()],
            'this_month' => [now()->startOfMonth(), now()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()],
            'this_year' => [now()->startOfYear(), now()],
            'custom' => [
                Carbon::parse($request->input('start_date')),
                Carbon::parse($request->input('end_date')),
            ],
            default => [now()->startOfMonth(), now()],
        };
    }
}
