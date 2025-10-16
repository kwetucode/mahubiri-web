<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\UserCodeVerification;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    /**
     * Send email verification code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerificationCode(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                Log::info("Email verification request for already verified email", ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Email déjà vérifié'
                ], 400);
            }

            // Créer un code de vérification
            $codeVerification = UserCodeVerification::createForUser($user, 'email_verification', 15);

            // Envoyer l'email avec le code
            $user->notify(new CustomVerifyEmail($codeVerification->code));

            Log::info("Code de vérification email envoyé", [
                'user_id' => $user->id,
                'email' => $user->email,
                'code_expires_at' => $codeVerification->expires_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code de vérification envoyé à votre email',
                'expires_in_minutes' => 15,
                'data' => [
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification code: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'envoi du code de vérification'
            ], 500);
        }
    }

    /**
     * Verify email with code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmailWithCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                Log::info("Email verification attempt for already verified email", ['user_id' => $user->id]);
                return response()->json([
                    'success' => true,
                    'message' => 'Email déjà vérifié',
                    'data' => [
                        'user' => new UserResource($user->load('role'))
                    ]
                ]);
            }

            // Vérifier le code
            $verification = UserCodeVerification::verifyCode(
                $user->email,
                $request->code,
                'email_verification'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré'
                ], 400);
            }

            // Marquer l'email comme vérifié
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));

                // Marquer le code comme utilisé et le supprimer de la DB
                $verification->markAsUsedAndDelete();

                // Envoyer l'email de bienvenue
                try {
                    $user->notify(new WelcomeNotification($user));
                    Log::info("Email de bienvenue envoyé", [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error("Échec de l'envoi de l'email de bienvenue: " . $e->getMessage(), [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    // Ne pas bloquer la vérification si l'email de bienvenue échoue
                }

                Log::info("Email vérifié avec succès", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'code_deleted' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email vérifié avec succès',
                'data' => [
                    'user' => new UserResource($user->load('role'))
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de la vérification email'
            ], 500);
        }
    }

    /**
     * Check if user's email is verified
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVerificationStatus(Request $request)
    {
        try {
            $user = $request->user();
            $isVerified = $user->hasVerifiedEmail();

            Log::info("Email verification status checked", [
                'user_id' => $user->id,
                'verified' => $isVerified
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification status retrieved successfully',
                'data' => [
                    'verified' => $isVerified,
                    'email' => $user->email,
                    'user' => new UserResource($user->load('role'))
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check verification status: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check verification status'
            ], 500);
        }
    }
}
