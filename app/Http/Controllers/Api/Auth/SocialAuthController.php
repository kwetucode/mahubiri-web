<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Authenticate user with social provider token (for Flutter app)
     *
     * This method is designed for mobile apps (Flutter) where the social login
     * is handled on the client side and the access token is sent to the API.
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

            // Get user info from provider using the access token
            $providerUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

            if (!$providerUser || !$providerUser->getEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve user information from ' . ucfirst($provider),
                ], 400);
            }

            // Find or create user
            $user = $this->findOrCreateUser($providerUser, $provider);

            // Generate Sanctum token
            $token = $user->createToken('mobile-app-token')->plainTextToken;

            //Send wellcome email
            $user->notify(new WelcomeNotification($user));
            Log::info("Email de bienvenue envoyé", [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with ' . ucfirst($provider),
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'role_type' => $user->role_type,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
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
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 500);
        }
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

            // Get user info from provider
            $providerUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

            if (!$providerUser || !$providerUser->getEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve user information from ' . ucfirst($provider),
                ], 400);
            }

            // Check if this social account is already linked to another user
            $existingUser = User::where($provider . '_id', $providerUser->getId())
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
                $provider . '_id' => $providerUser->getId(),
                $provider . '_token' => $accessToken,
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
                $provider . '_token' => null,
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

    /**
     * Find or create user from social provider data
     *
     * @param \Laravel\Socialite\Contracts\User $providerUser
     * @param string $provider
     * @return User
     */
    private function findOrCreateUser($providerUser, $provider)
    {
        // Check if user exists with this provider ID
        $user = User::where($provider . '_id', $providerUser->getId())->first();

        if ($user) {
            // Update the access token
            $user->update([
                $provider . '_token' => request('access_token'),
            ]);
            return $user;
        }

        // Check if user exists with this email
        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            // Link the social account to existing user
            $user->update([
                $provider . '_id' => $providerUser->getId(),
                $provider . '_token' => request('access_token'),
            ]);
            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $providerUser->getName() ?? $providerUser->getNickname() ?? 'User',
            'email' => $providerUser->getEmail(),
            'password' => Hash::make(Str::random(24)), // Random password for social users
            $provider . '_id' => $providerUser->getId(),
            $provider . '_token' => request('access_token'),
            'email_verified_at' => now(), // Social accounts are considered verified
            'role_id' => RoleType::USER,
        ]);

        return $user;
    }
}
