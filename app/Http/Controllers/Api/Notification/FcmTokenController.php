<?php

namespace App\Http\Controllers\Api\Notification;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteFcmTokenRequest;
use App\Http\Requests\StoreFcmTokenRequest;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    /**
     * Store or update FCM token for the authenticated user
     *
     * @param StoreFcmTokenRequest $request
     * @return JsonResponse
     */
    public function store(StoreFcmTokenRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            // Use updateOrCreate to handle token replacement
            $token = UserFcmToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fcm_token' => $validated['fcm_token']
                ],
                [
                    'device_type' => $validated['device_type'] ?? null
                ]
            );

            Log::info('FCM token registered', [
                'user_id' => $user->id,
                'token_id' => $token->id,
                'device_type' => $token->device_type
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $token->id,
                    'device_type' => $token->device_type,
                    'created_at' => $token->created_at->toISOString(),
                    'updated_at' => $token->updated_at->toISOString(),
                ],
                'message' => 'Token FCM enregistré avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de l\'enregistrement du token FCM.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Delete FCM token
     *
     * @param DeleteFcmTokenRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteFcmTokenRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            $deleted = UserFcmToken::where('user_id', $user->id)
                ->where('fcm_token', $validated['fcm_token'])
                ->delete();

            if ($deleted > 0) {
                Log::info('FCM token deleted', [
                    'user_id' => $user->id,
                    'deleted_count' => $deleted
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Token FCM supprimé avec succès.'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token FCM non trouvé.',
                    'errors' => [
                        'fcm_token' => ['Le token spécifié n\'existe pas.']
                    ]
                ], 404);
            }
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la suppression du token FCM.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Get all FCM tokens for the authenticated user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $tokens = $user->fcmTokens;

            $data = $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'device_type' => $token->device_type,
                    'created_at' => $token->created_at->toISOString(),
                    'updated_at' => $token->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Tokens FCM récupérés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des tokens FCM.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }
}
