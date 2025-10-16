<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetWebController extends Controller
{
    /**
     * Show password reset redirect page
     * This handles the link clicked from the email
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            Log::warning('Password reset link missing token or email', [
                'token_present' => !empty($token),
                'email_present' => !empty($email)
            ]);

            return $this->redirectToApp('reset-password-failed', [
                'error' => 'missing_params',
                'message' => 'Lien de réinitialisation invalide'
            ]);
        }

        Log::info('Password reset link accessed', [
            'email' => $email,
            'token_length' => strlen($token)
        ]);

        // Redirect to Flutter app with token and email
        return $this->redirectToApp('reset-password', [
            'token' => $token,
            'email' => $email,
            'message' => 'Veuillez entrer votre nouveau mot de passe'
        ]);
    }

    /**
     * Redirect to Flutter app with deep link
     *
     * @param string $path
     * @param array $params
     * @return \Illuminate\Http\Response
     */
    private function redirectToApp(string $path, array $params = [])
    {
        // Get the Flutter app deep link scheme from config
        $appScheme = config('app.flutter_scheme', 'mahubiri');

        // Build query parameters
        $queryString = http_build_query($params);

        // Create deep link URL
        $deepLink = "{$appScheme}://{$path}?{$queryString}";

        Log::info('Redirecting to Flutter app', [
            'deep_link' => $deepLink,
            'path' => $path,
            'params' => array_keys($params) // Log keys only for security
        ]);

        // Return a view that handles the redirect with fallback
        return response()->view('redirect-to-app', [
            'deepLink' => $deepLink,
            'appName' => config('app.name', 'Mahubiri'),
            'title' => $this->getTitleForPath($path),
            'message' => $params['message'] ?? '',
            'playStoreUrl' => config('app.play_store_url', '#'),
            'appStoreUrl' => config('app.app_store_url', '#'),
        ]);
    }

    /**
     * Get title based on path
     *
     * @param string $path
     * @return string
     */
    private function getTitleForPath(string $path): string
    {
        return match ($path) {
            'reset-password' => 'Réinitialisation du mot de passe',
            'reset-password-success' => 'Mot de passe réinitialisé',
            'reset-password-failed' => 'Erreur de réinitialisation',
            default => 'Réinitialisation du mot de passe',
        };
    }
}
