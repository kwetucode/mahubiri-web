<?php

namespace App\Console\Commands;

use App\Models\Sermon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BoostRandomSermons extends Command
{
    protected $signature = 'sermons:boost-random
                            {--count=2 : Number of sermons to boost (1 or 2)}
                            {--score=5000 : Popularity score to assign}
                            {--reset : Reset all previously boosted sermons before boosting new ones}';

    protected $description = 'Randomly boost 1 or 2 published sermons by assigning them a high popularity score';

    public function handle()
    {
        $count = min((int) $this->option('count'), 2);
        $score = (float) $this->option('score');

        if ($count < 1) {
            $this->error('Count must be at least 1.');
            return Command::FAILURE;
        }

        // Reset previously boosted sermons if requested
        if ($this->option('reset')) {
            $resetCount = Sermon::where('popularity_score', '>=', $score)
                ->update([
                    'popularity_score' => 0,
                    'popularity_calculated_at' => now(),
                ]);
            $this->info("🔄 Reset {$resetCount} previously boosted sermon(s).");
        }

        // Pick random published sermons
        $sermons = Sermon::where('is_published', true)
            ->where('popularity_score', '<', $score)
            ->inRandomOrder()
            ->take($count)
            ->get();

        if ($sermons->isEmpty()) {
            $this->warn('No eligible published sermons found to boost.');
            return Command::SUCCESS;
        }

        foreach ($sermons as $sermon) {
            $sermon->update([
                'popularity_score' => $score,
                'popularity_calculated_at' => now(),
            ]);

            $this->info("⭐ Boosted: \"{$sermon->title}\" (ID: {$sermon->id}) → score {$score}");

            Log::info('Sermon manually boosted', [
                'sermon_id' => $sermon->id,
                'title' => $sermon->title,
                'score' => $score,
            ]);
        }

        // Clear popular sermons cache so the change is visible immediately
        Cache::forget('home_popular_sermons');

        $this->info("✅ {$sermons->count()} sermon(s) boosted successfully!");

        return Command::SUCCESS;
    }
}
