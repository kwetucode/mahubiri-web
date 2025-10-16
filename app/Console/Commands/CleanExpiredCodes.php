<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCodeVerification;
use Illuminate\Support\Facades\Log;

class CleanExpiredCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codes:clean {--days=7 : Number of days to keep used/expired codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired and used verification codes from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("Nettoyage des codes de vérification...");

        // Supprimer les codes expirés ou utilisés depuis plus de X jours
        $deletedCount = UserCodeVerification::where(function ($query) use ($days) {
            $query->where('expires_at', '<', now())
                ->orWhere('is_used', true);
        })
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("✓ {$deletedCount} code(s) nettoyé(s) avec succès");

        Log::info('Codes de vérification nettoyés', [
            'deleted_count' => $deletedCount,
            'days_threshold' => $days
        ]);

        // Afficher les statistiques
        $activeCount = UserCodeVerification::where('is_used', false)
            ->where('expires_at', '>', now())
            ->count();

        $this->info("Codes actifs restants : {$activeCount}");

        return Command::SUCCESS;
    }
}
