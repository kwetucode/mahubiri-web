<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class LoginController extends Controller
{
    /**
     * Login user and return access token
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(LoginRequest $request)
    {
        try {
            // Validation is automatically handled by LoginRequest
            $loginField = $request->getLoginField();
            $loginValue = $request->input('login');
            $credentials = [
                $loginField => $loginValue,
                'password' => $request->input('password')
            ];
            // Attempt to authenticate the user
            $user = User::where($loginField, $loginValue)->first();
            if (!$user) {
                Log::warning("Login failed: Invalid login", [$loginField => $loginValue]);
                return response()->json([
                    'success' => false,
                    'message' => $loginField === 'email' ? 'Email incorrecte' : 'Identifiant incorrecte'
                ], 401);
            }
            // Now attempt to log in
            if (!Auth::attempt($credentials)) {
                Log::warning("Login failed: Invalid password", [$loginField => $loginValue]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe incorrecte'
                ], 401);
            }

            $user = User::where($loginField, $loginValue)->firstOrFail();
            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                Log::warning("Login failed: Email not verified", ['user' => $user]);
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez vérifier votre adresse email avant de vous connecter.'
                ], 403);
            }

            // Check if 2FA is enabled — require TOTP challenge
            if ($user->hasEnabledTwoFactorAuthentication()) {
                $challengeToken = Str::random(64);
                Cache::put("2fa_challenge:{$challengeToken}", $user->id, now()->addMinutes(5));

                Log::info("Login: 2FA challenge issued", ['user_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Authentification à deux facteurs requise.',
                    'data' => [
                        'two_factor_required' => true,
                        'challenge_token' => $challengeToken,
                    ]
                ], 200);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info("Login successful", ['user' => $user]);
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'connexion utilisateur', [
                'user_login' => $request->input('login'),
                'login_field' => $loginField ?? null
            ], 500);
        }
    }
}
