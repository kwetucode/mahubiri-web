# UploadSermonService - Guide d'utilisation

Le service `UploadSermonService` a été amélioré pour supporter à la fois les chaînes base64 et les fichiers uploadés directement. Il supporte également différents types de stockage (covers, logos, avatars, etc.). Cela permet une utilisation flexible depuis Vue.js ou d'autres clients.

## Fonctionnalités

### 1. Upload d'images

#### Avec une chaîne base64 (Vue.js, fetch, etc.)

```php
$uploadService = new UploadSermonService();

// Sermon covers (par défaut)
$base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAA...';
$imageUrl = $uploadService->handleImageUpload($base64Image);

// Church logos
$logoUrl = $uploadService->handleImageUpload($base64Image, 'church_logos');

// User avatars
$avatarUrl = $uploadService->handleImageUpload($base64Image, 'avatars');
```

#### Avec un fichier uploadé (formulaire HTML)

```php
$uploadService = new UploadSermonService();

// Sermon covers (par défaut)
$uploadedFile = $request->file('image');
$imageUrl = $uploadService->handleImageUpload($uploadedFile);

// Church logos
$logoUrl = $uploadService->handleImageUpload($uploadedFile, 'church_logos');

// User avatars
$avatarUrl = $uploadService->handleImageUpload($uploadedFile, 'user_avatars');
```

### 2. Upload d'audio

#### Avec une chaîne base64

```php
$uploadService = new UploadSermonService();

$base64Audio = 'data:audio/mp3;base64,SUQzBAAAAAABEVRYWFgAAAAtAAAD...';
$audioUrl = $uploadService->handleAudioUpload($base64Audio);
```

#### Avec un fichier uploadé

```php
$uploadService = new UploadSermonService();

$uploadedFile = $request->file('audio');
$audioUrl = $uploadService->handleAudioUpload($uploadedFile);
```

## Validation

### Images

-   **Formats supportés**: JPEG, PNG, WebP, GIF
-   **Taille maximum**: 5MB
-   **Types MIME acceptés**: `image/jpeg`, `image/jpg`, `image/png`, `image/webp`, `image/gif`

### Audio

-   **Formats supportés**: MP3, WAV, M4A, AAC, OGG
-   **Taille maximum**: 50MB
-   **Types MIME acceptés**: `audio/mpeg`, `audio/mp3`, `audio/wav`, `audio/x-wav`, `audio/mp4`, `audio/m4a`, `audio/aac`, `audio/ogg`

## Types de stockage supportés

Le service supporte plusieurs types de stockage pour organiser vos fichiers :

```php
$uploadService = new UploadSermonService();

// Obtenir la liste complète des types supportés
$storageTypes = $uploadService->getSupportedStorageTypes();
/*
Retourne :
[
    'covers' => 'Sermon covers (sermons/covers/)',
    'sermon_covers' => 'Sermon covers (sermons/covers/)',
    'logos' => 'Church logos (churches/logos/)',
    'church_logos' => 'Church logos (churches/logos/)',
    'church_covers' => 'Church covers (churches/covers/)',
    'avatars' => 'User avatars (users/avatars/)',
    'user_avatars' => 'User avatars (users/avatars/)',
    'profiles' => 'User profiles (users/profiles/)',
    'images' => 'Generic images (uploads/images/)',
    'files' => 'Generic files (uploads/files/)',
]
*/
```

### Structure des dossiers générée

-   **Sermons** : `sermons/covers/2025/01/15/`
-   **Churches** : `churches/logos/2025/01/15/` ou `churches/covers/2025/01/15/`
-   **Users** : `users/avatars/2025/01/15/` ou `users/profiles/2025/01/15/`
-   **Generic** : `uploads/images/2025/01/15/` ou `uploads/files/2025/01/15/`

## Méthodes utilitaires

### Vérification du type d'entrée

```php
$uploadService = new UploadSermonService();

// Vérifier si c'est une chaîne base64
if ($uploadService->isBase64String($input)) {
    echo "C'est une chaîne base64";
}

// Vérifier si c'est un fichier uploadé
if ($uploadService->isUploadedFile($input)) {
    echo "C'est un fichier uploadé";
}

// Obtenir le type de fichier (audio ou image)
$fileType = $uploadService->getFileType($input); // 'audio' ou 'image'
```

## Exemple d'utilisation dans un contrôleur Laravel

```php
<?php

namespace App\Http\Controllers;

use App\Services\UploadSermonService;
use Illuminate\Http\Request;

class SermonController extends Controller
{
    private UploadSermonService $uploadService;

    public function __construct(UploadSermonService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function store(Request $request)
    {
        try {
            // Support pour base64 depuis Vue.js
            if ($request->has('cover_image_base64')) {
                $coverUrl = $this->uploadService->handleImageUpload($request->cover_image_base64, 'covers');
            }
            // Support pour fichier uploadé depuis un formulaire
            elseif ($request->hasFile('cover_image')) {
                $coverUrl = $this->uploadService->handleImageUpload($request->file('cover_image'), 'covers');
            }

            // Même logique pour l'audio
            if ($request->has('audio_base64')) {
                $audioUrl = $this->uploadService->handleAudioUpload($request->audio_base64);
            }
            elseif ($request->hasFile('audio_file')) {
                $audioUrl = $this->uploadService->handleAudioUpload($request->file('audio_file'));
            }

            // Créer le sermon avec les URLs
            // ...

        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Exemple pour les églises
    public function storeChurch(Request $request)
    {
        try {
            // Logo de l'église
            if ($request->has('logo_base64')) {
                $logoUrl = $this->uploadService->handleImageUpload($request->logo_base64, 'church_logos');
            } elseif ($request->hasFile('logo')) {
                $logoUrl = $this->uploadService->handleImageUpload($request->file('logo'), 'church_logos');
            }

            // Cover de l'église
            if ($request->has('cover_base64')) {
                $coverUrl = $this->uploadService->handleImageUpload($request->cover_base64, 'church_covers');
            } elseif ($request->hasFile('cover')) {
                $coverUrl = $this->uploadService->handleImageUpload($request->file('cover'), 'church_covers');
            }

            // Créer l'église avec les URLs
            // ...

        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Exemple pour les utilisateurs
    public function updateProfile(Request $request)
    {
        try {
            // Avatar utilisateur
            if ($request->has('avatar_base64')) {
                $avatarUrl = $this->uploadService->handleImageUpload($request->avatar_base64, 'user_avatars');
            } elseif ($request->hasFile('avatar')) {
                $avatarUrl = $this->uploadService->handleImageUpload($request->file('avatar'), 'user_avatars');
            }

            // Mettre à jour le profil utilisateur
            // ...

        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

## Exemple côté Vue.js

```javascript
// Upload avec base64
const uploadSermon = async (audioBlob, coverImage) => {
    // Convertir le blob audio en base64
    const audioBase64 = await blobToBase64(audioBlob);

    // Convertir l'image en base64 (si nécessaire)
    const coverBase64 = await imageToBase64(coverImage);

    const response = await fetch("/api/sermons", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
            title: "Mon sermon",
            audio_base64: audioBase64,
            cover_image_base64: coverBase64,
        }),
    });

    return response.json();
};

// Ou upload avec FormData (fichiers)
const uploadSermonWithFiles = async (audioFile, coverFile) => {
    const formData = new FormData();
    formData.append("title", "Mon sermon");
    formData.append("audio_file", audioFile);
    formData.append("cover_image", coverFile);

    const response = await fetch("/api/sermons", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
        },
        body: formData,
    });

    return response.json();
};
```

## Gestion des erreurs

Le service lance des `InvalidArgumentException` dans les cas suivants :

-   Format de fichier non supporté
-   Taille de fichier trop importante
-   Données base64 invalides
-   Échec de l'upload du fichier
-   Type MIME non autorisé

Assurez-vous de capturer ces exceptions dans vos contrôleurs pour renvoyer des messages d'erreur appropriés à vos clients.
