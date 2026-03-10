<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RequestEmailChangeRequest;
use App\Http\Requests\VerifyEmailChangeRequest;
use App\Http\Resources\UserResource;
use App\Models\EmailChangeCode;
use App\Models\User;
use App\Notifications\EmailChangeCodeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class UserSecurityController extends Controller
{
    /**
     * Get user security settings
     *
     * @return JsonResponse
     */
    public function getSecuritySettings(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if there's a pending email change request
            $pendingEmailChange = EmailChangeCode::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->first();

            $settings = [
                'email' => $user->email,
                'email_verified' => $user->email_verified_at !== null,
                'has_password' => !empty($user->password),
                'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'two_factor_confirmed' => ! is_null($user->two_factor_confirmed_at),
                'pending_email_change' => $pendingEmailChange ? [
                    'new_email' => $pendingEmailChange->new_email,
                    'requested_at' => $pendingEmailChange->created_at->toISOString(),
                    'expires_at' => $pendingEmailChange->expires_at->toISOString(),
                ] : null,
                'last_password_change' => $user->updated_at->toISOString(),
                'account_created' => $user->created_at->toISOString(),
            ];

            Log::info('Security settings retrieved', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Paramètres de sécurité récupérés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la récupération des paramètres de sécurité.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Change user password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect.',
                    'errors' => [
                        'current_password' => ['Le mot de passe actuel est incorrect.']
                    ]
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info('User password changed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe changé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors du changement de mot de passe.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Request email change by sending verification code
     *
     * @param RequestEmailChangeRequest $request
     * @return JsonResponse
     */
    public function requestEmailChange(RequestEmailChangeRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe est incorrect.',
                    'errors' => [
                        'password' => ['Le mot de passe est incorrect.']
                    ]
                ], 422);
            }

            // Check if new email already exists
            if (User::where('email', $request->new_email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé par un autre compte.',
                    'errors' => [
                        'new_email' => ['Cet email est déjà utilisé.']
                    ]
                ], 422);
            }

            // Delete any existing pending email change requests for this user
            EmailChangeCode::where('user_id', $user->id)->delete();

            // Generate 6-digit verification code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store the email change request
            $emailChangeCode = EmailChangeCode::create([
                'user_id' => $user->id,
                'new_email' => $request->new_email,
                'code' => $code,
                'expires_at' => now()->addMinutes(15), // Code expires in 15 minutes
            ]);

            // Send verification code to the new email
            try {
                // Use Notification facade to send to arbitrary email
                Notification::route('mail', $request->new_email)
                    ->notify(new EmailChangeCodeNotification($code, $request->new_email));

                Log::info('Email change code sent', [
                    'user_id' => $user->id,
                    'old_email' => $user->email,
                    'new_email' => $request->new_email,
                    'code' => $code
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email change code', [
                    'user_id' => $user->id,
                    'new_email' => $request->new_email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Delete the code since email failed
                $emailChangeCode->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.',
                    'errors' => [
                        'email' => ['Impossible d\'envoyer l\'email de vérification.']
                    ]
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Code de vérification envoyé à {$request->new_email}."
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la demande de changement d\'email.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Verify email change code and update email
     *
     * @param VerifyEmailChangeRequest $request
     * @return JsonResponse
     */
    public function verifyEmailChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Find the email change request
            $emailChangeCode = EmailChangeCode::where('user_id', $user->id)
                ->where('new_email', $request->new_email)
                ->where('code', $request->code)
                ->where('expires_at', '>', now())
                ->first();

            if (!$emailChangeCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification invalide ou expiré.',
                    'errors' => [
                        'code' => ['Code de vérification invalide ou expiré.']
                    ]
                ], 422);
            }

            // Check if new email is still available
            if (User::where('email', $request->new_email)->where('id', '!=', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé par un autre compte.',
                    'errors' => [
                        'new_email' => ['Cet email est déjà utilisé.']
                    ]
                ], 422);
            }

            $oldEmail = $user->email;

            // Update user email
            $user->email = $request->new_email;
            $user->email_verified_at = now(); // Mark new email as verified
            $user->save();

            // Delete the used code
            $emailChangeCode->delete();

            // Delete any other pending requests
            EmailChangeCode::where('user_id', $user->id)->delete();

            Log::info('User email changed successfully', [
                'user_id' => $user->id,
                'old_email' => $oldEmail,
                'new_email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email changé avec succès.',
                'user' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'Erreur lors de la vérification du changement d\'email.',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }
}
