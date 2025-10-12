<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(Lang::get('Réinitialisation de votre mot de passe'))
            ->greeting(Lang::get('Bonjour !'))
            ->line(Lang::get('Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.'))
            ->line(Lang::get('Votre code de réinitialisation est :'))
            ->line('**' . $this->token . '**')
            ->line(Lang::get('Ou vous pouvez cliquer sur le bouton ci-dessous :'))
            ->action(Lang::get('Réinitialiser le mot de passe'), $resetUrl)
            ->line(Lang::get('Ce lien de réinitialisation expirera dans :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
            ->line(Lang::get('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action n\'est requise.'))
            ->salutation(Lang::get('Cordialement,') . "\n" . config('app.name'));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable): string
    {
        // Pour une API, on retourne le token dans l'URL
        // L'application Flutter utilisera ce token avec l'endpoint /api/auth/password/reset
        return config('app.frontend_url', config('app.url')) . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];
    }
}
