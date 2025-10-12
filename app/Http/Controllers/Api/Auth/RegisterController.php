<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Create a new user account
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        // Validation is automatically handled by RegisterRequest
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role_id' =>  1, // Default role
            ]);
            event(new Registered($user));

            // Send verification email (Laravel sends it automatically when User implements MustVerifyEmail)
            //$user->sendEmailVerificationNotification();

            // Create token for the user after registration
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info("User registered successfully", ['user' => $user]);
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de l\'inscription utilisateur', [
                'email' => $request->input('email'),
                'request_data' => $request->except(['password'])
            ]);
        }
    }
}
