<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-welcome 
                            {user? : The user ID to send welcome email to}
                            {--all : Send welcome email to all verified users who haven\'t received it}
                            {--force : Force send even if already sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome email to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user');
        $sendToAll = $this->option('all');
        $force = $this->option('force');

        try {
            if ($userId) {
                // Envoyer à un utilisateur spécifique
                return $this->sendToUser($userId, $force);
            } elseif ($sendToAll) {
                // Envoyer à tous les utilisateurs éligibles
                return $this->sendToAllEligibleUsers($force);
            } else {
                $this->error('Veuillez spécifier un user ID ou utiliser --all');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Erreur: {$e->getMessage()}");
            Log::error('Welcome email command failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'send_to_all' => $sendToAll,
                'force' => $force
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Send welcome email to a specific user
     */
    private function sendToUser(int $userId, bool $force): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("Utilisateur avec ID {$userId} non trouvé");
            return Command::FAILURE;
        }

        if (!$user->hasVerifiedEmail()) {
            $this->error("L'email de l'utilisateur {$user->email} n'est pas vérifié");
            return Command::FAILURE;
        }

        if (!$force && $user->hasWelcomeEmailBeenSent()) {
            $this->warn("L'email de bienvenue a déjà été envoyé à {$user->email} le {$user->welcome_email_sent_at}");
            return Command::SUCCESS;
        }

        $user->notify(new WelcomeNotification($user));
        $user->markWelcomeEmailAsSent();

        $this->info("Email de bienvenue envoyé à {$user->email}");

        Log::info('Welcome email sent via command', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'force' => $force
        ]);

        return Command::SUCCESS;
    }

    /**
     * Send welcome email to all eligible users
     */
    private function sendToAllEligibleUsers(bool $force): int
    {
        $query = User::whereNotNull('email_verified_at');

        if (!$force) {
            $query->whereNull('welcome_email_sent_at');
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur éligible trouvé');
            return Command::SUCCESS;
        }

        $this->info("Envoi de l'email de bienvenue à {$users->count()} utilisateur(s)...");

        $sent = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                $user->notify(new WelcomeNotification($user));
                $user->markWelcomeEmailAsSent();
                $sent++;
                $this->line("✓ Email envoyé à {$user->email}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Erreur pour {$user->email}: {$e->getMessage()}");
                Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Terminé: {$sent} email(s) envoyé(s), {$errors} erreur(s)");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
