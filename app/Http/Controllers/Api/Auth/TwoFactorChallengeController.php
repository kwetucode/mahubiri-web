<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class TwoFactorChallengeController extends Controller
{
    /**
     * Verify a 2FA code during login and issue an auth token.
     */
    public function __invoke(Request $request, TwoFactorAuthenticationProvider $provider): JsonResponse
    {
        $request->validate([
            'challenge_token' => ['required', 'string'],
            'code' => ['required_without:recovery_code', 'nullable', 'string'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        try {
            $cacheKey = "2fa_challenge:{$request->challenge_token}";
            $userId = Cache::pull($cacheKey);

            if (! $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le jeton de défi est invalide ou expiré. Veuillez vous reconnecter.',
                ], 401);
            }

            $user = User::findOrFail($userId);

            // Try TOTP code first
            if ($request->filled('code')) {
                $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

                if (! $provider->verify($secret, $request->code)) {
                    // Re-store challenge so user can retry
                    Cache::put($cacheKey, $userId, now()->addMinutes(5));

                    return response()->json([
                        'success' => false,
                        'message' => 'Le code d\'authentification est invalide.',
                        'errors' => ['code' => ['Le code d\'authentification est invalide.']],
                    ], 422);
                }
            }
            // Try recovery code
            elseif ($request->filled('recovery_code')) {
                $recoveryCodes = $user->recoveryCodes();

                if (! in_array($request->recovery_code, $recoveryCodes)) {
                    Cache::put($cacheKey, $userId, now()->addMinutes(5));

                    return response()->json([
                        'success' => false,
                        'message' => 'Le code de récupération est invalide.',
                        'errors' => ['recovery_code' => ['Le code de récupération est invalide.']],
                    ], 422);
                }

                // Replace used recovery code
                $user->replaceRecoveryCode($request->recovery_code);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info("Login successful after 2FA", ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $token,
                ],
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'vérification 2FA', [
                'challenge_token' => $request->challenge_token,
            ], 500);
        }
    }
}
