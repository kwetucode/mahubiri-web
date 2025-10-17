<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Services\UploadSermonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserAvatarController extends Controller
{
    /**
     * @var UploadSermonService
     */
    private UploadSermonService $uploadService;

    /**
     * UserAvatarController constructor.
     */
    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Update user avatar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'avatar_base64' => 'required_without:avatar_file|string',
                'avatar_file' => 'required_without:avatar_base64|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'avatar_base64.required_without' => 'Veuillez fournir une image en base64 ou un fichier.',
                'avatar_file.required_without' => 'Veuillez fournir un fichier ou une image en base64.',
                'avatar_file.image' => 'Le fichier doit être une image.',
                'avatar_file.mimes' => 'L\'image doit être au format: jpeg, png, jpg ou gif.',
                'avatar_file.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            ]);

            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->avatar_url) {
                $this->uploadService->deleteFile($user->avatar_url, 'images');
                Log::info('Old avatar deleted', [
                    'user_id' => $user->id,
                    'old_avatar' => $user->avatar_url,
                ]);
            }

            // Upload new avatar
            $avatarUrl = null;
            if (!empty($validated['avatar_base64'])) {
                $avatarUrl = $this->uploadService->handleImageUpload($validated['avatar_base64'], 'avatars');
            } elseif (!empty($validated['avatar_file'])) {
                $avatarUrl = $this->uploadService->handleImageUpload($validated['avatar_file'], 'avatars');
            }

            // Update user
            $user->update([
                'avatar_url' => $avatarUrl,
            ]);

            Log::info('User avatar updated', [
                'user_id' => $user->id,
                'avatar_url' => $avatarUrl,
            ]);

            return $this->successResponse(
                [
                    'avatar_url' => $avatarUrl,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
                'Avatar mis à jour avec succès'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'mise à jour de l\'avatar',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Remove user avatar
     *
     * @return JsonResponse
     */
    public function removeAvatar(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user->avatar_url) {
                return $this->errorResponse('Aucun avatar à supprimer', 404);
            }

            // Delete avatar file
            $this->uploadService->deleteFile($user->avatar_url, 'images');

            // Update user
            $user->update([
                'avatar_url' => null,
            ]);

            Log::info('User avatar removed', [
                'user_id' => $user->id,
            ]);

            return $this->successResponse(
                null,
                'Avatar supprimé avec succès'
            );
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto(
                $e,
                'suppression de l\'avatar',
                [
                    'user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * Return a standardized success response
     */
    private function successResponse($data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    /**
     * Return a standardized error response
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
