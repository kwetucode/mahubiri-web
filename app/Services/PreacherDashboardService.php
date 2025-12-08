<?php

namespace App\Services;

use App\Models\PreacherProfile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreacherDashboardService
{
    /**
     * Get dashboard statistics for a preacher profile
     *
     * @param int $preacherProfileId
     * @return array
     */
    public function getDashboardStats(int $preacherProfileId): array
    {
        $preacherProfile = PreacherProfile::findOrFail($preacherProfileId);

        return [
            'profile_info' => $this->getProfileInfo($preacherProfile),
            'sermons_summary' => $this->getSermonsSummary($preacherProfile),
            'sermons_by_month' => $this->getSermonsPublishedByMonth($preacherProfile),
            'listens_by_day' => $this->getListensByDay($preacherProfile),
            'listens_by_month' => $this->getListensByMonth($preacherProfile),
            'listens_by_period' => $this->getListensByPeriod($preacherProfile),
        ];
    }

    /**
     * Get profile information
     *
     * @param PreacherProfile $preacherProfile
     * @return array
     */
    private function getProfileInfo(PreacherProfile $preacherProfile): array
    {
        return [
            'ministry_name' => $preacherProfile->ministry_name,
            'ministry_type' => $preacherProfile->ministry_type?->description ?? $preacherProfile->ministry_type,
            'member_since' => $preacherProfile->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Get sermons summary (total, published, drafts)
     *
     * @param PreacherProfile $preacherProfile
     * @return array
     */
    private function getSermonsSummary(PreacherProfile $preacherProfile): array
    {
        $totalSermons = $preacherProfile->sermons()->count();
        $publishedSermons = $preacherProfile->sermons()->where('is_published', true)->count();
        $draftSermons = $totalSermons - $publishedSermons;

        return [
            'total' => $totalSermons,
            'published' => $publishedSermons,
            'drafts' => $draftSermons,
        ];
    }

    /**
     * Get sermons published by month (last 12 months)
     *
     * @param PreacherProfile $preacherProfile
     * @return array
     */
    private function getSermonsPublishedByMonth(PreacherProfile $preacherProfile): array
    {
        $startDate = Carbon::now()->subMonths(12);

        $sermonsByMonth = $preacherProfile->sermons()
            ->where('is_published', true)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as sermons_count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => sprintf('%04d-%02d', $item->year, $item->month),
                    'month_name' => Carbon::createFromDate($item->year, $item->month, 1)->format('F Y'),
                    'sermons_published' => $item->sermons_count,
                ];
            })
            ->toArray();

        return $sermonsByMonth;
    }

    /**
     * Get listens count by day (last 30 days)
     *
     * @param PreacherProfile $preacherProfile
     * @return array
     */
    private function getListensByDay(PreacherProfile $preacherProfile): array
    {
        $sermonIds = $preacherProfile->sermons()->pluck('id');
        $startDate = Carbon::now()->subDays(30);

        $listensByDay = DB::table('sermon_views')
            ->select(
                DB::raw('DATE(played_at) as date'),
                DB::raw('COUNT(*) as listens_count')
            )
            ->whereIn('sermon_id', $sermonIds)
            ->where('played_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'day_name' => Carbon::parse($item->date)->format('l'),
                    'listens' => $item->listens_count,
                ];
            })
            ->toArray();

        return $listensByDay;
    }

    /**
     * Get listens count by month (last 12 months)
     *
     * @param PreacherProfile $preacherProfile
     * @return array
     */
    private function getListensByMonth(PreacherProfile $preacherProfile): array
    {
        $sermonIds = $preacherProfile->sermons()->pluck('id');
        $startDate = Carbon::now()->subMonths(12);

        $listensByMonth = DB::table('sermon_views')
            ->select(
                DB::raw('YEAR(played_at) as year'),
                DB::raw('MONTH(played_at) as month'),
                DB::raw('COUNT(*) as listens_count')
            )
            ->whereIn('sermon_id', $sermonIds)
            ->where('played_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => sprintf('%04d-%02d', $item->year, $item->month),
                    'month_name' => Carbon::createFromDate($item->year, $item->month, 1)->format('F Y'),
                    'listens' => $item->listens_count,
                ];
            })
            ->toArray();

        return $listensByMonth;
    }

    /**
     * Get listens count by custom period
     *
     * @param PreacherProfile $preacherProfile
     * @param string $period 'week', 'month', 'quarter', 'year'
     * @return array
     */
    private function getListensByPeriod(PreacherProfile $preacherProfile, string $period = 'week'): array
    {
        $sermonIds = $preacherProfile->sermons()->pluck('id');

        $periods = [
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subMonths(3),
            'year' => Carbon::now()->subYear(),
        ];

        $result = [];
        foreach ($periods as $periodName => $startDate) {
            $count = DB::table('sermon_views')
                ->whereIn('sermon_id', $sermonIds)
                ->where('played_at', '>=', $startDate)
                ->count();

            $result[$periodName] = $count;
        }

        // Add total all time
        $result['all_time'] = DB::table('sermon_views')
            ->whereIn('sermon_id', $sermonIds)
            ->count();

        return $result;
    }
}
