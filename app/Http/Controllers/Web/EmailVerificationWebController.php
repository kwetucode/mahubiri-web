<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationWebController extends Controller
{
    /**
     * Handle email verification from link clicked in email
     * Redirects to Flutter app with verification result
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        try {
            // Get user by ID from the route parameter
            $user = User::findOrFail($request->route('id'));

            // Verify the hash
            if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                Log::warning('Invalid email verification hash', [
                    'user_id' => $user->id,
                    'expected_hash' => sha1($user->getEmailForVerification()),
                    'received_hash' => $request->route('hash')
                ]);

                // Redirect to Flutter app with error
                return $this->redirectToApp('verification-failed', [
                    'error' => 'invalid_link',
                    'message' => 'Le lien de vérification est invalide'
                ]);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                Log::info('Email verification attempt for already verified email', [
                    'user_id' => $user->id
                ]);

                // Redirect to Flutter app with already verified status
                return $this->redirectToApp('verification-success', [
                    'status' => 'already_verified',
                    'message' => 'Votre email est déjà vérifié',
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            // Mark email as verified
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                Log::info('Email verified successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            // Redirect to Flutter app with success
            return $this->redirectToApp('verification-success', [
                'status' => 'verified',
                'message' => 'Votre email a été vérifié avec succès',
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found for email verification', [
                'user_id' => $request->route('id')
            ]);

            return $this->redirectToApp('verification-failed', [
                'error' => 'user_not_found',
                'message' => 'Utilisateur non trouvé'
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $request->route('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->redirectToApp('verification-failed', [
                'error' => 'server_error',
                'message' => 'Une erreur est survenue lors de la vérification'
            ]);
        }
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
            'params' => $params
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
            'verification-success' => 'Vérification réussie',
            'verification-failed' => 'Erreur de vérification',
            default => 'Vérification d\'email',
        };
    }
}
