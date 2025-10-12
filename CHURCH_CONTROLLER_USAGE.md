# ChurchController avec UploadSermonService - Guide d'utilisation

Le `ChurchController` a été refactorisé pour utiliser le `UploadSermonService`, offrant une gestion d'images plus robuste et flexible.

## Nouvelles fonctionnalités

### 1. Upload automatique lors de la création/mise à jour

Le contrôleur utilise maintenant le type de stockage `'church_logos'` pour organiser les fichiers dans `churches/logos/YYYY/MM/DD/`.

### 2. Nouvelles routes ajoutées

```
PATCH /api/churches/{id}/logo    - Mettre à jour le logo
PATCH /api/churches/{id}/cover   - Mettre à jour la cover
DELETE /api/churches/{id}/logo   - Supprimer le logo
DELETE /api/churches/{id}/cover  - Supprimer la cover
```

## Exemples d'utilisation

### Créer une église avec logo

```javascript
const createChurch = async (churchData) => {
    const response = await fetch("/api/churches/", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
            name: "Ma Super Église",
            description: "Description de mon église",
            address: "123 Rue de la Paix",
            logo: "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAA...", // Base64
        }),
    });

    return response.json();
};
```

### Mettre à jour uniquement le logo

```javascript
const updateLogo = async (churchId, logoBase64) => {
    const response = await fetch(`/api/churches/${churchId}/logo`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
            logo: logoBase64,
        }),
    });

    return response.json();
};
```

### Mettre à jour uniquement la cover

```javascript
const updateCover = async (churchId, coverBase64) => {
    const response = await fetch(`/api/churches/${churchId}/cover`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
            cover: coverBase64,
        }),
    });

    return response.json();
};
```

### Supprimer le logo

```javascript
const removeLogo = async (churchId) => {
    const response = await fetch(`/api/churches/${churchId}/logo`, {
        method: "DELETE",
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });

    return response.json();
};
```

### Supprimer la cover

```javascript
const removeCover = async (churchId) => {
    const response = await fetch(`/api/churches/${churchId}/cover`, {
        method: "DELETE",
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });

    return response.json();
};
```

## Structure des fichiers générée

```
storage/app/public/
└── churches/
    ├── logos/
    │   └── 2025/01/15/
    │       └── abc123def456...._logo.jpg
    └── covers/
        └── 2025/01/15/
            └── xyz789abc123...._church_cover.jpg
```

## Réponses API

### Succès de création/mise à jour

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Ma Super Église",
        "description": "Description de mon église",
        "address": "123 Rue de la Paix",
        "logo_url": "/storage/churches/logos/2025/01/15/abc123...._logo.jpg",
        "cover_url": "/storage/churches/covers/2025/01/15/xyz789...._church_cover.jpg",
        "created_by": {
            "id": 1,
            "name": "John Doe"
        }
    },
    "message": "Church created successfully"
}
```

### Erreur d'upload

```json
{
    "success": false,
    "message": "Logo upload failed: Invalid base64 image format. Expected format: data:image/{type};base64,{data}"
}
```

## Validation

-   **Formats supportés** : JPEG, PNG, WebP, GIF
-   **Taille maximum** : 5MB par image
-   **Format attendu** : Base64 avec préfixe `data:image/{type};base64,`

## Avantages de la refactorisation

1. **Organisation automatique** : Fichiers organisés par date dans des dossiers spécifiques
2. **Validation robuste** : Types MIME, taille des fichiers, format base64
3. **Gestion d'erreurs améliorée** : Messages d'erreur explicites
4. **Suppression automatique** : Anciens fichiers supprimés lors de la mise à jour
5. **Code plus maintenable** : Réutilisation du service centralisé
6. **Flexibilité** : Support futur pour des fichiers uploadés directement

## Migration depuis l'ancien système

Les URLs existantes dans la base de données continueront de fonctionner. Les nouvelles images seront stockées dans la nouvelle structure organisée.
