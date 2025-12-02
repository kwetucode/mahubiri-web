<?php

namespace App\Http\Controllers\Api\Notification;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationSettingsRequest;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Models\UserNotificationSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationSettingsController extends Controller
{
    /**
     * Get user notification settings
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $settings = $user->notificationSettings;

            // If no settings exist, return defaults
            if (!$settings) {
                $data = UserNotificationSettings::getDefaults();
            } else {
                $data = [
                    'new_sermon' => $settings->new_sermon,
                    'new_church' => $settings->new_church,
                    'new_announcement' => $settings->new_announcement,
                    'push_enabled' => $settings->push_enabled,
                    'email_enabled' => $settings->email_enabled,
                ];
            }

            Log::info('Notification settings retrieved', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Paramètres de notification récupérés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des paramètres de notification.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Create or update all notification settings
     *
     * @param StoreNotificationSettingsRequest $request
     * @return JsonResponse
     */
    public function store(StoreNotificationSettingsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            // Use updateOrCreate to avoid duplicates
            $settings = UserNotificationSettings::updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );

            Log::info('Notification settings saved', [
                'user_id' => $user->id,
                'settings' => $validated
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'new_sermon' => $settings->new_sermon,
                    'new_church' => $settings->new_church,
                    'new_announcement' => $settings->new_announcement,
                    'push_enabled' => $settings->push_enabled,
                    'email_enabled' => $settings->email_enabled,
                ],
                'message' => 'Paramètres de notification enregistrés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de l\'enregistrement des paramètres de notification.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Update specific notification settings
     *
     * @param UpdateNotificationSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            // Get or create settings
            $settings = UserNotificationSettings::firstOrCreate(
                ['user_id' => $user->id],
                UserNotificationSettings::getDefaults()
            );

            // Update only provided fields
            $settings->update($validated);

            Log::info('Notification settings updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validated)
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'new_sermon' => $settings->new_sermon,
                    'new_church' => $settings->new_church,
                    'new_announcement' => $settings->new_announcement,
                    'push_enabled' => $settings->push_enabled,
                    'email_enabled' => $settings->email_enabled,
                ],
                'message' => 'Paramètres de notification mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la mise à jour des paramètres de notification.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }
}
