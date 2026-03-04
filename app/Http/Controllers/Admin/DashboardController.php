<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\RoleType;
use App\Models\Church;
use App\Models\Sermon;
use App\Models\SermonView;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Church Admin → dedicated church dashboard
        if ($user->role_id === RoleType::CHURCH_ADMIN) {
            return $this->churchDashboard($user);
        }

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
     * Church Admin dashboard with church-specific stats.
     */
    private function churchDashboard($user)
    {
        $church = $user->church;

        if (!$church) {
            abort(403, 'Aucune église associée à votre compte.');
        }

        $churchId = $church->id;

        $totalSermons = Sermon::where('church_id', $churchId)->count();
        $publishedSermons = Sermon::where('church_id', $churchId)->where('is_published', true)->count();
        $draftSermons = $totalSermons - $publishedSermons;
        $totalViews = Sermon::where('church_id', $churchId)->withCount('views')->get()->sum('views_count');

        $sermonsThisWeek = Sermon::where('church_id', $churchId)
            ->where('created_at', '>=', now()->startOfWeek())->count();
        $sermonsThisMonth = Sermon::where('church_id', $churchId)
            ->where('created_at', '>=', now()->startOfMonth())->count();

        // Latest sermons (5)
        $latestSermons = Sermon::where('church_id', $churchId)
            ->with('category')
            ->withCount('views')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn (Sermon $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'preacher_name' => $s->preacher_name,
                'category_name' => $s->category?->name,
                'is_published' => (bool) $s->is_published,
                'views_count' => $s->views_count,
                'duration_formatted' => $s->duration_formatted,
                'created_at' => $s->created_at->format('d/m/Y'),
                'created_at_human' => $s->created_at->diffForHumans(),
            ]);

        // Top sermons by views (5)
        $topSermons = Sermon::where('church_id', $churchId)
            ->where('is_published', true)
            ->withCount('views')
            ->orderByDesc('views_count')
            ->take(5)
            ->get()
            ->map(fn (Sermon $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'preacher_name' => $s->preacher_name,
                'views_count' => $s->views_count,
            ]);

        return Inertia::render('Admin/ChurchDashboard', [
            'church' => [
                'id' => $church->id,
                'name' => $church->name,
            ],
            'stats' => [
                'totalSermons' => $totalSermons,
                'publishedSermons' => $publishedSermons,
                'draftSermons' => $draftSermons,
                'totalViews' => $totalViews,
                'sermonsThisWeek' => $sermonsThisWeek,
                'sermonsThisMonth' => $sermonsThisMonth,
            ],
            'diskUsage' => $this->getDiskUsageStatistics($churchId),
            'latestSermons' => $latestSermons,
            'topSermons' => $topSermons,
        ]);
    }

    /**
     * Return chart data for a given model, grouped by day.
     */
    public function chartData(Request $request)
    {
        $user = Auth::user();
        $isChurchAdmin = $user->role_id === RoleType::CHURCH_ADMIN;

        // Church admin can only view sermons and views charts
        $allowedTypes = $isChurchAdmin ? 'sermons,views' : 'users,churches,sermons,views';

        $request->validate([
            'type' => "required|in:{$allowedTypes}",
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
            'views' => SermonView::query(),
        };

        // Scope to church if CHURCH_ADMIN
        if ($isChurchAdmin && in_array($type, ['sermons', 'views']) && $user->church) {
            if ($type === 'sermons') {
                $query->where('church_id', $user->church->id);
            } elseif ($type === 'views') {
                $query->whereHas('sermon', fn ($q) => $q->where('church_id', $user->church->id));
            }
        }

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
            'sermons' => Sermon::query()
                ->when($isChurchAdmin && $user->church, fn ($q) => $q->where('church_id', $user->church->id))
                ->whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()])
                ->count(),
            'views' => SermonView::query()
                ->when($isChurchAdmin && $user->church, fn ($q) => $q->whereHas('sermon', fn ($sq) => $sq->where('church_id', $user->church->id)))
                ->whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()])
                ->count(),
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
     * Get disk usage statistics for a church.
     */
    private function getDiskUsageStatistics(int $churchId): array
    {
        $quotaBytes = 3 * 1024 * 1024 * 1024; // 3 GB

        $totalSizeBytes = Sermon::where('church_id', $churchId)->sum('size') ?? 0;

        $usedGB = round($totalSizeBytes / (1024 * 1024 * 1024), 2);
        $usedPercentage = $totalSizeBytes > 0
            ? round(($totalSizeBytes / $quotaBytes) * 100, 2)
            : 0;
        $remainingBytes = max(0, $quotaBytes - $totalSizeBytes);
        $remainingGB = round($remainingBytes / (1024 * 1024 * 1024), 2);

        $totalSermons = Sermon::where('church_id', $churchId)->count();
        $avgSizeMB = $totalSermons > 0
            ? round(($totalSizeBytes / $totalSermons) / (1024 * 1024), 2)
            : 0;

        $status = 'normal';
        if ($usedPercentage >= 90) {
            $status = 'critical';
        } elseif ($usedPercentage >= 75) {
            $status = 'warning';
        }

        return [
            'quotaGB' => 3.0,
            'usedGB' => $usedGB,
            'usedMB' => round($totalSizeBytes / (1024 * 1024), 2),
            'remainingGB' => $remainingGB,
            'usedPercentage' => $usedPercentage,
            'remainingPercentage' => round(100 - $usedPercentage, 2),
            'avgSizeMB' => $avgSizeMB,
            'totalSermons' => $totalSermons,
            'status' => $status,
        ];
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
