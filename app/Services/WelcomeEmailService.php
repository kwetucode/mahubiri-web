<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiExceptionHandler;

class WelcomeEmailService
{
    /**
     * Send welcome email to a user
     *
     * @param User $user
     * @param bool $force Force send even if already sent
     * @return array
     */
    public function sendWelcomeEmail(User $user, bool $force = false): array
    {
        try {
            // Vérifier si l'email est vérifié
            if (!$user->hasVerifiedEmail()) {
                return [
                    'success' => false,
                    'message' => 'L\'email de l\'utilisateur doit être vérifié avant d\'envoyer l\'email de bienvenue.',
                    'code' => 'EMAIL_NOT_VERIFIED'
                ];
            }

            // Vérifier si l'email de bienvenue a déjà été envoyé
            if (!$force && $user->hasWelcomeEmailBeenSent()) {
                return [
                    'success' => false,
                    'message' => 'L\'email de bienvenue a déjà été envoyé à cet utilisateur.',
                    'code' => 'ALREADY_SENT',
                    'sent_at' => $user->welcome_email_sent_at
                ];
            }

            // Envoyer l'email de bienvenue
            $user->notify(new WelcomeNotification($user));
            $user->markWelcomeEmailAsSent();

            Log::info('Welcome email sent manually', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'force' => $force,
                'sent_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Email de bienvenue envoyé avec succès.',
                'sent_at' => now()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de bienvenue.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send welcome emails to multiple users
     *
     * @param array $userIds
     * @param bool $force
     * @return array
     */
    public function sendWelcomeEmailToMultiple(array $userIds, bool $force = false): array
    {
        $results = [
            'sent' => 0,
            'errors' => 0,
            'skipped' => 0,
            'details' => []
        ];

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $result = $this->sendWelcomeEmail($user, $force);

            if ($result['success']) {
                $results['sent']++;
            } elseif (isset($result['code']) && $result['code'] === 'ALREADY_SENT') {
                $results['skipped']++;
            } else {
                $results['errors']++;
            }

            $results['details'][] = [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Get statistics about welcome emails
     *
     * @return array
     */
    public function getWelcomeEmailStats(): array
    {
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $welcomeEmailsSent = User::whereNotNull('welcome_email_sent_at')->count();
        $pendingWelcomeEmails = User::whereNotNull('email_verified_at')
            ->whereNull('welcome_email_sent_at')
            ->count();

        return [
            'total_users' => $totalUsers,
            'verified_users' => $verifiedUsers,
            'welcome_emails_sent' => $welcomeEmailsSent,
            'pending_welcome_emails' => $pendingWelcomeEmails,
            'completion_rate' => $verifiedUsers > 0 ? round(($welcomeEmailsSent / $verifiedUsers) * 100, 2) : 0
        ];
    }
}
