<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCodeVerification;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send verification code to user's email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Trouver l'utilisateur
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Créer un code de vérification
            $codeVerification = UserCodeVerification::createForUser($user, 'password_reset', 15);

            // Envoyer l'email avec le code
            $user->notify(new CustomResetPasswordNotification($codeVerification->code));

            Log::info('Code de réinitialisation envoyé', [
                'email' => $request->email,
                'code_expires_at' => $codeVerification->expires_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code de vérification envoyé à votre email',
                'expires_in_minutes' => 15
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de l\'envoi du code de réinitialisation', [
                'email' => $request->input('email')
            ]);
        }
    }

    /**
     * Verify the reset code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $verification = UserCodeVerification::verifyCode(
                $request->email,
                $request->code,
                'password_reset'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Code vérifié avec succès',
                'valid' => true
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la vérification du code', [
                'email' => $request->input('email')
            ]);
        }
    }

    /**
     * Reset user password using verification code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|string|size:6',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier le code
            $verification = UserCodeVerification::verifyCode(
                $request->email,
                $request->code,
                'password_reset'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré'
                ], 400);
            }

            // Trouver l'utilisateur
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Réinitialiser le mot de passe
            $user->forceFill([
                'password' => Hash::make($request->password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            // Marquer le code comme utilisé et le supprimer de la DB
            $verification->markAsUsedAndDelete();

            // Déclencher l'événement
            // Revoke all Sanctum tokens after password reset
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            // Déclencher l'événement PasswordReset
            event(new PasswordReset($user));

            Log::info('Mot de passe réinitialisé avec succès', [
                'email' => $request->email,
                'user_id' => $user->id,
                'code_deleted' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la réinitialisation du mot de passe', [
                'email' => $request->input('email')
            ]);
        }
    }
}
