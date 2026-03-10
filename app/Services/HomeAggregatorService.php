<?php

namespace App\Services;

use App\Http\Resources\CategorySermonResource;
use App\Http\Resources\ChurchResource;
use App\Http\Resources\PreacherProfileResource;
use App\Http\Resources\SermonResource;
use App\Models\CategorySermon;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Models\SermonFavorite;
use App\Models\SermonView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeAggregatorService
{
    /**
     * Aggregate all home page data in a single call.
     *
     * Sections that rarely change (categories, churches, preachers, popular_sermons)
     * are cached. User-specific data (user_stats, is_favorite) is always fresh.
     *
     * @return array
     */
    public function aggregate(): array
    {
        $user = Auth::user();

        // ── Parallel-ish fetching (cached sections resolve from cache instantly) ──

        $recentSermons = $this->getRecentSermons();
        $popularSermons = $this->getPopularSermons();
        $churches = $this->getChurches();
        $preachers = $this->getPreachers();
        $categories = $this->getCategories();
        $userStats = $this->getUserStats($user);

        // ── Batch is_favorite resolution for all sermon IDs ──

        $allSermonIds = $recentSermons->pluck('id')
            ->merge($popularSermons->pluck('id'))
            ->unique()
            ->values();

        $favoriteIds = $this->getFavoriteSermonIds($user->id, $allSermonIds->toArray());

        // Stamp is_favorite onto each sermon model so the Resource picks it up
        $stampFavorite = function ($sermon) use ($favoriteIds) {
            $sermon->is_favorite = $favoriteIds->contains($sermon->id);
        };

        $recentSermons->each($stampFavorite);
        $popularSermons->each($stampFavorite);

        return [
            'recent_sermons'  => SermonResource::collection($recentSermons),
            'popular_sermons' => SermonResource::collection($popularSermons),
            'churches'        => ChurchResource::collection($churches),
            'preachers'       => PreacherProfileResource::collection($preachers),
            'categories'      => CategorySermonResource::collection($categories),
            'user_stats'      => $userStats,
        ];
    }

    /**
     * Fetch a single section independently (used for graceful degradation).
     */
    public function getSection(string $section): mixed
    {
        $user = Auth::user();

        return match ($section) {
            'recent_sermons'  => SermonResource::collection($this->getRecentSermons()),
            'popular_sermons' => SermonResource::collection($this->getPopularSermons()),
            'churches'        => ChurchResource::collection($this->getChurches()),
            'preachers'       => PreacherProfileResource::collection($this->getPreachers()),
            'categories'      => CategorySermonResource::collection($this->getCategories()),
            'user_stats'      => $this->getUserStats($user),
            default           => [],
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  Recent sermons — fresh (max 60 s cache)
    // ─────────────────────────────────────────────────────────────

    private function getRecentSermons()
    {
        return Cache::remember('home_recent_sermons', 60, function () {
            $limit = 5;

            $featuredChurch = Church::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->first();

            $featuredSermon = null;

            if ($featuredChurch) {
                $featuredSermon = Sermon::with(['church.createdBy', 'category'])
                    ->withCount('views')
                    ->published()
                    ->where('church_id', $featuredChurch->id)
                    ->orderByDesc('created_at')
                    ->first();
            }

            $excludeIds = $featuredSermon ? [$featuredSermon->id] : [];
            $recentLimit = $featuredSermon ? $limit - 1 : $limit;

            $recentSermons = Sermon::with(['church.createdBy', 'category'])
                ->withCount('views')
                ->published()
                ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->orderByDesc('created_at')
                ->take($recentLimit)
                ->get();

            // Insert featured sermon at position 2 (after the first recent sermon)
            if ($featuredSermon && $recentSermons->isNotEmpty()) {
                $result = collect();
                $result->push($recentSermons->shift());
                $result->push($featuredSermon);
                return $result->concat($recentSermons);
            }

            return $recentSermons;
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Popular sermons — cached 5 min
    // ─────────────────────────────────────────────────────────────

    private function getPopularSermons()
    {
        return Cache::remember('home_popular_sermons', 300, function () {
            return Sermon::with(['church.createdBy', 'category'])
                ->published()
                ->withCount(['favoritedBy', 'views'])
                ->addSelect([
                    'completed_unique_users' => SermonView::selectRaw('COUNT(DISTINCT user_id)')
                        ->whereColumn('sermon_id', 'sermons.id')
                        ->where('completed', true),
                ])
                ->where(function ($q) {
                    $q->having('completed_unique_users', '>=', 2)
                      ->orWhere('popularity_score', '>=', 1000);
                })
                ->where('popularity_score', '>=', 0)
                ->orderByDesc('popularity_score')
                ->take(10)
                ->get();
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Churches — cached 10 min
    // ─────────────────────────────────────────────────────────────

    private function getChurches()
    {
        return Cache::remember('home_churches', 600, function () {
            return Church::active()
                ->whereHas('sermons', fn($q) => $q->where('is_published', true))
                ->with(['createdBy'])
                ->withCount(['sermons' => fn($q) => $q->where('is_published', true)])
                ->withCount(['sermonViews as total_views'])
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->get();
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Preachers — cached 5 min
    // ─────────────────────────────────────────────────────────────

    private function getPreachers()
    {
        return Cache::remember('home_preachers', 300, function () {
            return PreacherProfile::active()
                ->with('user')
                ->withCount(['sermons' => fn($q) => $q->where('is_published', true)])
                ->withCount(['sermonViews as total_views'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Categories — cached 10 min
    // ─────────────────────────────────────────────────────────────

    private function getCategories()
    {
        return Cache::remember('home_categories', 600, function () {
            return CategorySermon::withCount(['sermons' => fn($q) => $q->where('is_published', true)])
                ->having('sermons_count', '>', 0)
                ->orderBy('name')
                ->get();
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  User stats — never cached (per-user, real-time)
    // ─────────────────────────────────────────────────────────────

    private function getUserStats($user): array
    {
        $listeningAgg = SermonView::where('user_id', $user->id)
            ->select([
                DB::raw('COUNT(DISTINCT sermon_id) as sermons_listened'),
                DB::raw('COALESCE(SUM(duration_played), 0) as total_duration'),
                DB::raw('COUNT(DISTINCT CASE WHEN completed = 1 THEN sermon_id END) as completed_sermons'),
                DB::raw('MAX(played_at) as last_played_at'),
            ])
            ->first();

        $favoritesCount = SermonFavorite::where('user_id', $user->id)->count();

        $sermonsListened = (int) ($listeningAgg->sermons_listened ?? 0);
        $totalDuration   = (int) ($listeningAgg->total_duration ?? 0);

        return [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'avatar_url' => $user->avatar_url ? asset($user->avatar_url) : null,
            ],
            'listening_stats' => [
                'sermons_listened_count'       => $sermonsListened,
                'total_listening_time_seconds'  => $totalDuration,
                'total_listening_time_formatted' => $this->formatDuration($totalDuration),
                'favorites_count'              => $favoritesCount,
                'completed_sermons_count'      => (int) ($listeningAgg->completed_sermons ?? 0),
            ],
            'activity' => [
                'last_activity_at'  => $listeningAgg->last_played_at,
                'is_active_listener' => $sermonsListened > 0,
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Batch favorite check — single query for all sermon IDs
    // ─────────────────────────────────────────────────────────────

    /**
     * @return \Illuminate\Support\Collection<int> IDs of sermons favorited by user
     */
    private function getFavoriteSermonIds(int $userId, array $sermonIds)
    {
        if (empty($sermonIds)) {
            return collect();
        }

        return SermonFavorite::where('user_id', $userId)
            ->whereIn('sermon_id', $sermonIds)
            ->pluck('sermon_id');
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────

    private function formatDuration(?int $seconds): string
    {
        if (!$seconds || $seconds <= 0) {
            return '0 min';
        }

        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remaining = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dmin', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%dmin %02ds', $minutes, $remaining);
        }

        return sprintf('%ds', $remaining);
    }
}
