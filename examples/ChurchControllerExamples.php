<?php

// Exemple d'utilisation du ChurchController refactorisé avec UploadSermonService

// === EXEMPLE DE CONTRÔLEUR PERSONNALISÉ ===

use App\Http\Controllers\Church\ChurchController;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Services\UploadSermonService;
use Illuminate\Support\Facades\Request;

class MyChurchController extends Controller
{
    private UploadSermonService $uploadService;

    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Exemple : Upload multiple d'images pour une église
     */
    public function uploadMultipleImages(Request $request, Church $church)
    {
        $request->validate([
            'logo' => 'nullable|string',  // Base64
            'cover' => 'nullable|string', // Base64
            'gallery.*' => 'nullable|string', // Array de base64
        ]);

        $uploadedImages = [];

        try {
            // Upload du logo
            if ($request->logo) {
                if ($church->logo_url) {
                    $this->uploadService->deleteFile($church->logo_url);
                }
                $uploadedImages['logo_url'] = $this->uploadService->handleImageUpload(
                    $request->logo,
                    'church_logos'
                );
            }

            // Upload de la cover
            if ($request->cover) {
                if ($church->cover_url) {
                    $this->uploadService->deleteFile($church->cover_url);
                }
                $uploadedImages['cover_url'] = $this->uploadService->handleImageUpload(
                    $request->cover,
                    'church_covers'
                );
            }

            // Upload de la galerie (images génériques)
            if ($request->gallery) {
                $galleryUrls = [];
                foreach ($request->gallery as $imageBase64) {
                    $galleryUrls[] = $this->uploadService->handleImageUpload(
                        $imageBase64,
                        'images' // Ou 'church_gallery' si vous l'ajoutez au service
                    );
                }
                $uploadedImages['gallery_urls'] = $galleryUrls;
            }

            // Mettre à jour l'église
            $church->update($uploadedImages);

            return response()->json([
                'success' => true,
                'data' => $church,
                'uploaded_images' => $uploadedImages,
                'message' => 'Images uploaded successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Exemple : Validation personnalisée avec le service
     */
    public function validateAndUploadLogo(Request $request, Church $church)
    {
        $request->validate([
            'logo' => 'required|string'
        ]);

        // Vérifications avant upload
        if (!$this->uploadService->isBase64String($request->logo)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image format. Please provide a valid base64 image.'
            ], 400);
        }

        try {
            $fileType = $this->uploadService->getFileType($request->logo);

            if ($fileType !== 'image') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only image files are allowed for logos.'
                ], 400);
            }

            // Proceed with upload
            $logoUrl = $this->uploadService->handleImageUpload($request->logo, 'church_logos');

            // Update church
            $church->update(['logo_url' => $logoUrl]);

            return response()->json([
                'success' => true,
                'data' => $church,
                'message' => 'Logo uploaded successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Exemple : Nettoyage des anciens fichiers
     */
    public function cleanupChurchImages(Church $church)
    {
        $filesToDelete = [];

        // Collecter tous les URLs d'images
        if ($church->logo_url) {
            $filesToDelete[] = $church->logo_url;
        }
        if ($church->cover_url) {
            $filesToDelete[] = $church->cover_url;
        }

        // Utiliser la suppression multiple du service
        $results = $this->uploadService->deleteMultipleFiles($filesToDelete);

        // Nettoyer la base de données
        $church->update([
            'logo_url' => null,
            'cover_url' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Church images cleaned up successfully',
            'deletion_results' => $results
        ]);
    }
}

// === EXEMPLE D'UTILISATION DEPUIS FRONTEND ===

/*
JavaScript/Vue.js example:

// Upload logo uniquement
const uploadLogo = async (churchId, logoFile) => {
    // Convertir le fichier en base64
    const base64 = await fileToBase64(logoFile);

    const response = await fetch(`/api/churches/${churchId}/logo`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ logo: base64 })
    });

    return response.json();
};

// Upload cover uniquement
const uploadCover = async (churchId, coverFile) => {
    const base64 = await fileToBase64(coverFile);

    const response = await fetch(`/api/churches/${churchId}/cover`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ cover: base64 })
    });

    return response.json();
};

// Fonction helper pour convertir File en base64
const fileToBase64 = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
};
*/
