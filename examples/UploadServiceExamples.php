<?php

// Exemple d'utilisation du UploadSermonService amélioré

use App\Services\UploadSermonService;

$uploadService = new UploadSermonService();

// === EXEMPLES D'UPLOAD D'IMAGES ===

// 1. Sermon covers (par défaut)
$coverUrl = $uploadService->handleImageUpload($base64Image);
$coverUrl = $uploadService->handleImageUpload($uploadedFile);

// 2. Church logos
$logoUrl = $uploadService->handleImageUpload($base64Image, 'church_logos');
$logoUrl = $uploadService->handleImageUpload($uploadedFile, 'church_logos');

// 3. User avatars
$avatarUrl = $uploadService->handleImageUpload($base64Image, 'user_avatars');
$avatarUrl = $uploadService->handleImageUpload($uploadedFile, 'user_avatars');

// 4. Church covers
$churchCoverUrl = $uploadService->handleImageUpload($base64Image, 'church_covers');
$churchCoverUrl = $uploadService->handleImageUpload($uploadedFile, 'church_covers');

// 5. Images génériques
$genericImageUrl = $uploadService->handleImageUpload($base64Image, 'images');
$genericImageUrl = $uploadService->handleImageUpload($uploadedFile, 'images');

// === VÉRIFICATIONS UTILES ===

// Obtenir les types de stockage supportés
$supportedTypes = $uploadService->getSupportedStorageTypes();
foreach ($supportedTypes as $type => $description) {
    echo "Type: {$type} - {$description}\n";
}

// Vérifier le type d'entrée
if ($uploadService->isBase64String($input)) {
    echo "C'est une chaîne base64\n";
}

if ($uploadService->isUploadedFile($input)) {
    echo "C'est un fichier uploadé\n";
}

// Obtenir le type de fichier
$fileType = $uploadService->getFileType($input); // 'audio' ou 'image'

// === EXEMPLES DE CHEMINS GÉNÉRÉS ===

/*
Avec 'covers' (par défaut) :
- Chemin : sermons/covers/2025/01/15/
- Fichier : a1b2c3d4e5f6...._cover.jpg
- URL complète : /storage/sermons/covers/2025/01/15/a1b2c3d4e5f6...._cover.jpg

Avec 'church_logos' :
- Chemin : churches/logos/2025/01/15/
- Fichier : a1b2c3d4e5f6...._logo.png
- URL complète : /storage/churches/logos/2025/01/15/a1b2c3d4e5f6...._logo.png

Avec 'user_avatars' :
- Chemin : users/avatars/2025/01/15/
- Fichier : a1b2c3d4e5f6...._avatar.jpg
- URL complète : /storage/users/avatars/2025/01/15/a1b2c3d4e5f6...._avatar.jpg
*/

// === GESTION D'ERREURS ===

try {
    // Upload avec type de stockage invalide
    $url = $uploadService->handleImageUpload($base64Image, 'invalid_type');
} catch (InvalidArgumentException $e) {
    echo "Erreur : " . $e->getMessage();
    // Affichera : "Unsupported storage type: invalid_type. Supported types: covers, sermon_covers, logos, ..."
}

try {
    // Upload avec format d'image non supporté
    $invalidBase64 = 'data:image/bmp;base64,....';
    $url = $uploadService->handleImageUpload($invalidBase64);
} catch (InvalidArgumentException $e) {
    echo "Erreur : " . $e->getMessage();
    // Affichera : "Unsupported image format: bmp. Allowed formats: jpg, jpeg, png, webp, gif"
}
