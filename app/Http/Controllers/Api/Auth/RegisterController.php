<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RoleType;
use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserCodeVerification;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\NewUserRegistered;
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

                // Notify all admins about new user registration
                $this->notifyAdmins($user);

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

    /**
     * Notify all admins about new user registration
     */
    private function notifyAdmins(User $user): void
    {
        try {
            // Get all admin users
            $admins = User::whereHas('role', function ($query) {
                $query->where('name', RoleType::ADMIN);
            })->get();

            Log::info('Looking for admins to notify', [
                'user_id' => $user->id,
                'admin_count' => $admins->count(),
                'admin_ids' => $admins->pluck('id')->toArray()
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admins found to notify about new user registration');
                return;
            }

            // Send notification to each admin
            foreach ($admins as $admin) {
                $admin->notify(new NewUserRegistered($user));
                Log::info('Notification sent to admin', [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'new_user_id' => $user->id
                ]);
            }

            Log::info('All admins notified about new user registration', [
                'user_id' => $user->id,
                'admin_count' => $admins->count()
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            Log::error('Failed to notify admins about new user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
