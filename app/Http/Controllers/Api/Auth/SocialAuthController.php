<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

class SocialAuthController extends Controller
{
    /**
     * Authenticate user with social provider token (for Flutter app)
     *
     * This method is designed for mobile apps (Flutter) where the social login
     * is handled on the client side. It handles both Firebase JWT tokens (Google)
     * and OAuth access tokens (Facebook).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialLogin(Request $request)
    {
        try {
            $request->validate([
                'provider' => 'required|in:google,facebook',
                'access_token' => 'required|string',
            ]);

            $provider = $request->provider;
            $accessToken = $request->access_token;

            // Determine token type and verify accordingly
            $providerUser = $this->verifyProviderToken($accessToken, $provider);

            if (!$providerUser || !isset($providerUser['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve user information from ' . ucfirst($provider),
                ], 400);
            }

            // Find or create user
            $user = $this->findOrCreateUserFromSocial($providerUser, $provider);

            // Generate Sanctum token
            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $token
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify provider token (Firebase JWT or OAuth Access Token)
     *
     * @param string $token
     * @param string $provider
     * @return array|null
     */
    private function verifyProviderToken(string $token, string $provider): ?array
    {
        // Check if it's a JWT token (has 2 dots) - Firebase token
        if (substr_count($token, '.') === 2) {
            return $this->verifyFirebaseToken($token, $provider);
        }

        // Otherwise, it's an OAuth access token - use Socialite
        return $this->verifyOAuthToken($token, $provider);
    }

    /**
     * Verify OAuth access token using Socialite (for Facebook)
     *
     * @param string $token
     * @param string $provider
     * @return array|null
     */
    private function verifyOAuthToken(string $token, string $provider): ?array
    {
        try {
            // Use Socialite to get user from access token
            $socialUser = Socialite::driver($provider)->userFromToken($token);

            if (!$socialUser) {
                throw new \Exception("Unable to retrieve user from {$provider}");
            }

            return [
                'id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar() ?? null,
                'email_verified' => true, // OAuth providers verify emails
            ];
        } catch (\Exception $e) {
            Log::error('Failed to verify OAuth token', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify Firebase JWT token and extract user info
     *
     * @param string $token
     * @param string $provider
     * @return array|null
     */
    private function verifyFirebaseToken(string $token, string $provider): ?array
    {
        try {
            $credentialsPath = config('firebase.credentials');

            if (!file_exists($credentialsPath)) {
                Log::error('Firebase credentials file not found', ['path' => $credentialsPath]);
                throw new \Exception('Firebase configuration error');
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $auth = $factory->createAuth();

            // Verify the token
            $verifiedIdToken = $auth->verifyIdToken($token);
            $uid = $verifiedIdToken->claims()->get('sub');

            // Get user from Firebase
            $firebaseUser = $auth->getUser($uid);

            // Extract user information
            return [
                'id' => $firebaseUser->uid,
                'email' => $firebaseUser->email,
                'name' => $firebaseUser->displayName ?? $firebaseUser->email,
                'avatar' => $firebaseUser->photoUrl ?? null,
                'email_verified' => $firebaseUser->emailVerified,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to verify Firebase token', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Find or create user from social provider data
     *
     * @param array $providerUser
     * @param string $provider
     * @return User
     */
    private function findOrCreateUserFromSocial(array $providerUser, string $provider): User
    {
        // Check if user exists with this provider ID
        $user = User::where($provider . '_id', $providerUser['id'])->first();

        if ($user) {
            return $user;
        }

        // Check if user exists with this email
        $user = User::where('email', $providerUser['email'])->first();

        if ($user) {
            // Link the social account to existing user
            $user->update([
                $provider . '_id' => $providerUser['id'],
            ]);
            return $user;
        }

        // Create new user with transaction for atomicity
        return DB::transaction(function () use ($providerUser, $provider) {
            // Create new user
            $user = User::create([
                'name' => $providerUser['name'],
                'email' => $providerUser['email'],
                'password' => Hash::make(Str::random(24)), // Random password for social users
                $provider . '_id' => $providerUser['id'],
                'email_verified_at' => $providerUser['email_verified'] ? now() : null,
                'role_id' => RoleType::USER,
            ]);

            // Verify user creation
            if (!$user || !$user->id) {
                throw new \Exception("Échec de la création de l'utilisateur via " . ucfirst($provider));
            }

            // Send welcome email and handle potential failure
            try {
                $user->notify(new WelcomeNotification($user));

                Log::info("User created via social auth", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'provider' => $provider
                ]);
            } catch (\Exception $emailException) {
                Log::warning("Failed to send welcome email for social auth user", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'provider' => $provider,
                    'error' => $emailException->getMessage()
                ]);
                // Don't throw exception, user creation is more important than welcome email
            }

            return $user;
        });
    }

    /**
     * Link social account to existing authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkSocialAccount(Request $request)
    {
        try {
            $request->validate([
                'provider' => 'required|in:google,facebook',
                'access_token' => 'required|string',
            ]);

            $user = Auth::user();
            $provider = $request->provider;
            $accessToken = $request->access_token;

            // Verify provider token and get user info
            $providerUser = $this->verifyProviderToken($accessToken, $provider);

            if (!$providerUser || !isset($providerUser['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve user information from ' . ucfirst($provider),
                ], 400);
            }

            // Check if this social account is already linked to another user
            $existingUser = User::where($provider . '_id', $providerUser['id'])
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ' . ucfirst($provider) . ' account is already linked to another user',
                ], 400);
            }

            // Link the social account
            $user->update([
                $provider . '_id' => $providerUser['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' account linked successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'google_linked' => !empty($user->google_id),
                        'facebook_linked' => !empty($user->facebook_id),
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to link account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink social account from authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkSocialAccount(Request $request)
    {
        try {
            $request->validate([
                'provider' => 'required|in:google,facebook',
            ]);

            $user = Auth::user();
            $provider = $request->provider;

            // Check if the account is linked
            if (empty($user->{$provider . '_id'})) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($provider) . ' account is not linked',
                ], 400);
            }

            // Unlink the social account
            $user->update([
                $provider . '_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' account unlinked successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'google_linked' => !empty($user->google_id),
                        'facebook_linked' => !empty($user->facebook_id),
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get social accounts status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSocialAccountsStatus(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'google_linked' => !empty($user->google_id),
                'facebook_linked' => !empty($user->facebook_id),
            ],
        ], 200);
    }
}
