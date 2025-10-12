<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Send email verification notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerificationEmail(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                Log::info("Email verification request for already verified email", ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified'
                ], 400);
            }

            $user->sendEmailVerificationNotification();
            Log::info("Email verification sent", ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully',
                'data' => [
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email'
            ], 500);
        }
    }

    /**
     * Verify user's email address
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        try {
            // Get user by ID from the route parameter
            $user = \App\Models\User::findOrFail($request->route('id'));

            // Verify the hash
            if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                throw new AuthorizationException('Invalid verification link.');
            }

            if ($user->hasVerifiedEmail()) {
                Log::info("Email verification attempt for already verified email", ['user_id' => $user->id]);
                return response()->json([
                    'success' => true,
                    'message' => 'Email already verified',
                    'data' => [
                        'user' => new UserResource($user->load('role'))
                    ]
                ]);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                Log::info("Email verified successfully", ['user_id' => $user->id, 'email' => $user->email]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'user' => new UserResource($user->load('role'))
                ]
            ]);
        } catch (AuthorizationException $e) {
            Log::error('Email verification failed - invalid link: ' . $e->getMessage(), [
                'user_id' => $request->route('id') ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage(), [
                'user_id' => $request->route('id') ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Email verification failed'
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
