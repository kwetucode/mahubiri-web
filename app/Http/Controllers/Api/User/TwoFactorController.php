<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorController extends Controller
{
    /**
     * Get 2FA status for the authenticated user.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'two_factor_confirmed' => ! is_null($user->two_factor_confirmed_at),
            ],
            'message' => 'Statut 2FA récupéré avec succès.',
        ]);
    }

    /**
     * Enable 2FA — generates secret & recovery codes.
     * Requires password confirmation.
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe est incorrect.',
                'errors' => ['password' => ['Le mot de passe est incorrect.']],
            ], 422);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'authentification à deux facteurs est déjà activée.',
            ], 409);
        }

        $enable($user);

        Log::info('2FA enabled for user', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code_svg' => $user->twoFactorQrCodeSvg(),
                'qr_code_url' => $user->twoFactorQrCodeUrl(),
                'recovery_codes' => $user->recoveryCodes(),
            ],
            'message' => 'Authentification à deux facteurs activée. Scannez le QR code puis confirmez avec un code TOTP.',
        ]);
    }

    /**
     * Confirm 2FA setup by verifying a TOTP code from the authenticator app.
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (is_null($user->two_factor_secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez d\'abord activer l\'authentification à deux facteurs.',
            ], 400);
        }

        if (! is_null($user->two_factor_confirmed_at)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'authentification à deux facteurs est déjà confirmée.',
            ], 409);
        }

        try {
            $confirm($user, $request->code);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le code TOTP est invalide.',
                'errors' => $e->errors(),
            ], 422);
        }

        Log::info('2FA confirmed for user', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Authentification à deux facteurs confirmée avec succès.',
        ]);
    }

    /**
     * Disable 2FA. Requires password confirmation.
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe est incorrect.',
                'errors' => ['password' => ['Le mot de passe est incorrect.']],
            ], 422);
        }

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'authentification à deux facteurs n\'est pas activée.',
            ], 400);
        }

        $disable($user);

        Log::info('2FA disabled for user', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Authentification à deux facteurs désactivée.',
        ]);
    }

    /**
     * Get current recovery codes (requires password).
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe est incorrect.',
                'errors' => ['password' => ['Le mot de passe est incorrect.']],
            ], 422);
        }

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'authentification à deux facteurs n\'est pas activée.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recovery_codes' => $user->recoveryCodes(),
            ],
            'message' => 'Codes de récupération récupérés.',
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe est incorrect.',
                'errors' => ['password' => ['Le mot de passe est incorrect.']],
            ], 422);
        }

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'authentification à deux facteurs n\'est pas activée.',
            ], 400);
        }

        $generate($user);

        Log::info('2FA recovery codes regenerated', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => [
                'recovery_codes' => $user->recoveryCodes(),
            ],
            'message' => 'Nouveaux codes de récupération générés.',
        ]);
    }
}
