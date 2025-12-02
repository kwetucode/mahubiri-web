<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserCodeVerification;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Create a new user account
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(RegisterRequest $request)
    {
        // Validation is automatically handled by RegisterRequest
        try {
            return DB::transaction(function () use ($request) {
                // Déterminer le role_id en fonction du champ is_church_admin
                $roleId = $request->boolean('is_church_admin', false)
                    ? \App\Enums\RoleType::CHURCH_ADMIN
                    : \App\Enums\RoleType::USER;

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'role_id' => $roleId,
                ]);

                // Créer et envoyer le code de vérification email
                $codeVerification = UserCodeVerification::createForUser($user, 'email_verification', 15);
                $user->notify(new CustomVerifyEmail($codeVerification->code));

                // Create token for the user after registration
                $token = $user->createToken('auth_token')->plainTextToken;

                Log::info("User registered successfully with verification code", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'code_expires_at' => $codeVerification->expires_at
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Inscription réussie. Un code de vérification a été envoyé à votre email.',
                    'data' => [
                        'user' => new UserResource($user->load('role')),
                        'token' => $token,
                        'verification_required' => true,
                        'code_expires_in_minutes' => 15,
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de l\'inscription utilisateur', [
                'email' => $request->input('email'),
                'request_data' => $request->except(['password'])
            ]);
        }
    }
}
