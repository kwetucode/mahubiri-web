<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Notifications\WelcomeNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        try {
            /** @var User $user */
            $user = $event->user;

            // Vérifier que l'utilisateur n'a pas déjà reçu l'email de bienvenue
            if (!$user->hasWelcomeEmailBeenSent()) {
                // Envoyer la notification de bienvenue
                $user->notify(new WelcomeNotification($user));
                // Marquer que l'email de bienvenue a été envoyé
                $user->markWelcomeEmailAsSent();

                Log::info('Welcome email sent', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'sent_at' => now()
                ]);
            } else {
                Log::info('Welcome email already sent', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'previously_sent_at' => $user->welcome_email_sent_at
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
