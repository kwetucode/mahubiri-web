<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RefreshTokenController extends Controller
{
    /**
     * Refresh the user's access token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                Log::warning("Token refresh failed: User not authenticated");
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                Log::warning("Token refresh failed: Email not verified", ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez vérifier votre adresse email avant de continuer.'
                ], 403);
            }

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $newToken = $user->createToken('auth_token')->plainTextToken;

            Log::info("Token refreshed successfully", ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Token rafraîchi avec succès',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $newToken
                ]
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'du rafraîchissement du token', [
                'user_id' => $request->user()?->id
            ], 500);
        }
    }
}
