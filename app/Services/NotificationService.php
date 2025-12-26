<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\UserNotificationSettings;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationService
{
    private $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = config('firebase.credentials');

            if (!file_exists($credentialsPath)) {
                Log::warning('Firebase credentials file not found', ['path' => $credentialsPath]);
                $this->messaging = null;
                return;
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->messaging = null;
        }
    }

    /**
     * Send notification to a specific user
     *
     * @param User $user
     * @param string $notificationType (new_sermon, new_church, new_announcement)
     * @param array $payload ['title' => string, 'body' => string, 'data' => array]
     * @return array ['success' => int, 'failed' => int, 'errors' => array]
     */
    public function sendToUser(User $user, string $notificationType, array $payload): array
    {
        $result = ['success' => 0, 'failed' => 0, 'errors' => []];

        // Check if Firebase is initialized
        if (!$this->messaging) {
            Log::error('Firebase not initialized', ['user_id' => $user->id]);
            $result['errors'][] = 'Firebase not initialized';
            return $result;
        }

        // Get user notification settings
        $settings = $user->notificationSettings;

        // If no settings exist, use defaults
        if (!$settings) {
            $settings = new UserNotificationSettings(UserNotificationSettings::getDefaults());
            $settings->user_id = $user->id;
        }

        // Check if notification type is enabled
        if (!$settings->isNotificationEnabled($notificationType)) {
            Log::info('Notification disabled for user', [
                'user_id' => $user->id,
                'notification_type' => $notificationType
            ]);
            return $result;
        }

        // Get all FCM tokens for the user
        $fcmTokens = $user->fcmTokens;

        if ($fcmTokens->isEmpty()) {
            Log::info('No FCM tokens found for user', ['user_id' => $user->id]);
            return $result;
        }

        // Send to each token
        foreach ($fcmTokens as $tokenRecord) {
            try {
                $message = CloudMessage::withTarget('token', $tokenRecord->fcm_token)
                    ->withNotification(
                        FirebaseNotification::create(
                            $payload['title'],
                            $payload['body']
                        )
                    )
                    ->withData($payload['data'] ?? []);

                $this->messaging->send($message);
                $result['success']++;

                Log::info('Notification sent successfully', [
                    'user_id' => $user->id,
                    'token_id' => $tokenRecord->id,
                    'notification_type' => $notificationType
                ]);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // Check if token is invalid and delete it
                if ($this->isInvalidToken($e)) {
                    Log::warning('Invalid FCM token, deleting', [
                        'user_id' => $user->id,
                        'token_id' => $tokenRecord->id,
                        'error' => $e->getMessage()
                    ]);
                    $tokenRecord->delete();
                }

                $result['failed']++;
                $result['errors'][] = $e->getMessage();

                Log::error('Failed to send notification', [
                    'user_id' => $user->id,
                    'token_id' => $tokenRecord->id,
                    'error' => $e->getMessage()
                ]);
            } catch (\Exception $e) {
                $result['failed']++;
                $result['errors'][] = $e->getMessage();

                Log::error('Unexpected error sending notification', [
                    'user_id' => $user->id,
                    'token_id' => $tokenRecord->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $result;
    }

    /**
     * Send notification to all members of a church
     *
     * @param int $churchId
     * @param string $notificationType
     * @param array $payload
     * @return array ['total_users' => int, 'success' => int, 'failed' => int]
     */
    public function sendToChurch(int $churchId, string $notificationType, array $payload): array
    {
        $result = ['total_users' => 0, 'success' => 0, 'failed' => 0];

        // Get all users who have tokens and are related to this church
        // Assuming users follow/are members of churches - adjust this query based on your app logic
        $users = User::whereHas('fcmTokens')
            ->get();

        $result['total_users'] = $users->count();

        foreach ($users as $user) {
            $userResult = $this->sendToUser($user, $notificationType, $payload);
            $result['success'] += $userResult['success'];
            $result['failed'] += $userResult['failed'];
        }

        Log::info('Notification sent to church', [
            'church_id' => $churchId,
            'notification_type' => $notificationType,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Send notification to all followers of a preacher
     *
     * @param int $preacherProfileId
     * @param string $notificationType
     * @param array $payload
     * @return array ['total_users' => int, 'success' => int, 'failed' => int]
     */
    public function sendToPreacher(int $preacherProfileId, string $notificationType, array $payload): array
    {
        $result = ['total_users' => 0, 'success' => 0, 'failed' => 0];

        /**
         * FUTURE ENHANCEMENT: Implement preacher followers system
         * 
         * When the followers functionality is implemented, replace the query below with:
         * $users = User::whereHas('followedPreachers', function($query) use ($preacherProfileId) {
         *     $query->where('preacher_profile_id', $preacherProfileId);
         * })->whereHas('fcmTokens')->get();
         * 
         * Current behavior: Sends notifications to all users with FCM tokens
         */
        $users = User::whereHas('fcmTokens')
            ->get();

        $result['total_users'] = $users->count();

        foreach ($users as $user) {
            $userResult = $this->sendToUser($user, $notificationType, $payload);
            $result['success'] += $userResult['success'];
            $result['failed'] += $userResult['failed'];
        }

        Log::info('Notification sent to preacher followers', [
            'preacher_profile_id' => $preacherProfileId,
            'notification_type' => $notificationType,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Check if the exception indicates an invalid token
     *
     * @param \Kreait\Firebase\Exception\MessagingException $e
     * @return bool
     */
    private function isInvalidToken(\Kreait\Firebase\Exception\MessagingException $e): bool
    {
        $errorCode = $e->getCode();
        $errorMessage = strtolower($e->getMessage());

        // Common invalid token error codes and messages
        $invalidTokenIndicators = [
            'registration-token-not-registered',
            'invalid-registration-token',
            'invalid-argument',
            'not found',
            'unregistered'
        ];

        foreach ($invalidTokenIndicators as $indicator) {
            if (str_contains($errorMessage, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send bulk notifications to multiple users
     *
     * @param array $userIds
     * @param string $notificationType
     * @param array $payload
     * @return array
     */
    public function sendToMultipleUsers(array $userIds, string $notificationType, array $payload): array
    {
        $result = ['total_users' => count($userIds), 'success' => 0, 'failed' => 0];

        $users = User::whereIn('id', $userIds)->with('fcmTokens', 'notificationSettings')->get();

        foreach ($users as $user) {
            $userResult = $this->sendToUser($user, $notificationType, $payload);
            $result['success'] += $userResult['success'];
            $result['failed'] += $userResult['failed'];
        }

        return $result;
    }

    /**
     * Send notification to all users with FCM tokens
     *
     * @param string $notificationType
     * @param array $payload
     * @return array
     */
    public function sendToAllUsers(string $notificationType, array $payload): array
    {
        $result = ['total_users' => 0, 'success' => 0, 'failed' => 0];

        // Get all users who have tokens
        $users = User::whereHas('fcmTokens')
            ->with('fcmTokens', 'notificationSettings')
            ->get();

        $result['total_users'] = $users->count();

        foreach ($users as $user) {
            $userResult = $this->sendToUser($user, $notificationType, $payload);
            $result['success'] += $userResult['success'];
            $result['failed'] += $userResult['failed'];
        }

        Log::info('Notification sent to all users', [
            'notification_type' => $notificationType,
            'result' => $result
        ]);

        return $result;
    }
}
