<?php

namespace App\Console\Commands;

use App\Models\Sermon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateSermonPopularity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sermons:calculate-popularity
                            {--sermon= : Calculate for specific sermon ID}
                            {--force : Force recalculation even if recently calculated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate popularity scores for sermons based on views, favorites, recency, and completion rate';

    /**
     * Popularity score weights
     */
    private const WEIGHT_FAVORITES = 30;
    private const WEIGHT_VIEWS = 10;
    private const WEIGHT_RECENCY = 20;
    private const WEIGHT_COMPLETION = 10;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting sermon popularity calculation...');

        $specificSermonId = $this->option('sermon');
        $force = $this->option('force');

        try {
            if ($specificSermonId) {
                $this->calculateForSermon($specificSermonId);
            } else {
                $this->calculateForAllSermons($force);
            }

            $this->info('✅ Popularity calculation completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error calculating popularity: ' . $e->getMessage());
            Log::error('Sermon popularity calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Calculate popularity for all sermons
     */
    private function calculateForAllSermons(bool $force = false): void
    {
        $query = Sermon::query();

        // Skip recently calculated sermons unless forced
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('popularity_calculated_at')
                    ->orWhere('popularity_calculated_at', '<', now()->subHour());
            });
        }

        $sermons = $query->get();
        $this->info("📊 Processing {$sermons->count()} sermons...");

        $progressBar = $this->output->createProgressBar($sermons->count());
        $progressBar->start();

        foreach ($sermons as $sermon) {
            $this->calculateScore($sermon);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Calculate popularity for a specific sermon
     */
    private function calculateForSermon(int $sermonId): void
    {
        $sermon = Sermon::findOrFail($sermonId);
        $this->info("📊 Calculating popularity for sermon: {$sermon->title}");
        $this->calculateScore($sermon);
    }

    /**
     * Calculate and update popularity score for a sermon
     */
    private function calculateScore(Sermon $sermon): void
    {
        // 1. Count favorites
        $favoritesCount = $sermon->favoritedBy()->count();

        // 2. Count views
        $viewsCount = $sermon->views()->count();

        // 3. Calculate recency boost
        $recencyBoost = $this->calculateRecencyBoost($sermon);

        // 4. Calculate completion rate
        $completionRate = $this->calculateCompletionRate($sermon);

        // 5. Calculate composite score
        $score = ($favoritesCount * self::WEIGHT_FAVORITES) +
            ($viewsCount * self::WEIGHT_VIEWS) +
            ($recencyBoost * self::WEIGHT_RECENCY) +
            ($completionRate * 100 * self::WEIGHT_COMPLETION);

        // 6. Update sermon
        $sermon->update([
            'popularity_score' => round($score, 2),
            'popularity_calculated_at' => now(),
        ]);

        Log::info('Sermon popularity updated', [
            'sermon_id' => $sermon->id,
            'title' => $sermon->title,
            'score' => round($score, 2),
            'favorites' => $favoritesCount,
            'views' => $viewsCount,
            'recency_boost' => $recencyBoost,
            'completion_rate' => round($completionRate, 2),
        ]);
    }

    /**
     * Calculate recency boost (0-100 points)
     * - Last 7 days: 100 points
     * - Last 30 days: 50 points
     * - Last 90 days: 25 points
     * - Older: 0 points
     */
    private function calculateRecencyBoost(Sermon $sermon): float
    {
        $daysOld = now()->diffInDays($sermon->created_at);

        if ($daysOld <= 7) {
            return 100;
        } elseif ($daysOld <= 30) {
            return 50;
        } elseif ($daysOld <= 90) {
            return 25;
        }

        return 0;
    }

    /**
     * Calculate completion rate (0.0 to 1.0)
     * Percentage of views that were completed
     */
    private function calculateCompletionRate(Sermon $sermon): float
    {
        $totalViews = $sermon->views()->count();

        if ($totalViews === 0) {
            return 0;
        }

        $completedViews = $sermon->views()->where('completed', true)->count();

        return $completedViews / $totalViews;
    }
}
