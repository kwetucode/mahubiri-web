<?php

namespace App\Http\Controllers\Api\Church;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChurchResource;
use App\Models\Church;
use App\Services\UploadSermonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateLogoChurchController extends Controller
{
    /**
     * Upload service for handling file uploads
     */
    private UploadSermonService $uploadService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }


    /**
     * Update church logo
     */
    public function updateLogo(Request $request, Church $church): JsonResponse
    {
        $request->validate([
            'logo' => 'required|string', // Base64 image
        ]);

        try {
            // Delete old logo if exists
            if ($church->logo_url) {
                $this->uploadService->deleteFile($church->logo_url);
            }

            // Upload new logo
            $logoUrl = $this->uploadService->handleImageUpload($request->logo, 'church_logos');

            // Update church with new logo URL
            $church->update(['logo_url' => $logoUrl]);
            $church->load('createdBy');

            return response()->json([
                'success' => true,
                'data' => new ChurchResource($church),
                'message' => 'Church logo updated successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return ApiExceptionHandler::auto($e, 'Logo upload failed:', [
                'logo' => $request->input('logo')
            ]);
        }
    }
    /**
     * Remove church logo
     */
    public function removeLogo(Church $church): JsonResponse
    {

        try {
            // Delete logo if exists
            if ($church->logo_url) {
                $this->uploadService->deleteFile($church->logo_url);
                $church->update(['logo_url' => null]);
            }

            $church->load('createdBy');

            return response()->json([
                'success' => true,
                'data' => new ChurchResource($church),
                'message' => 'Church logo removed successfully'
            ]);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'removal of church logo', [
                'church_id' => $church->id
            ]);
        }
    }
}
