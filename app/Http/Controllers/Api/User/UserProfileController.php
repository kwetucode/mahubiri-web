<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{


    /**
     * Get authenticated user information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $request->bearerToken(),
                    'user' => new UserResource($request->user()->load('role')),

                ]
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'récupération des informations utilisateur', [
                'user_id' => $request->user()->id ?? null
            ]);
        }
    }

    // Update user profile
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
            ]);

            $user->update($validated);

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validated),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'mise à jour du profil utilisateur', [
                'user_id' => $request->user()->id ?? null,
                'request_data' => $request->except(['password'])
            ]);
        }
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();
            if ($token ?? method_exists($token, 'delete')) {
                Log::info('User logged out', ['user' => $request->user()]);
                $token->delete();
            }
            Log::info('Logout successful', ['user' => $request->user()]);
            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'déconnexion utilisateur', [
                'user_id' => $request->user()->id ?? null
            ]);
        }
    }
}
