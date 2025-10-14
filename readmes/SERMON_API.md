# API Sermon - Documentation

## Vue d'ensemble

L'API Sermon permet aux utilisateurs de publier et gérer des prédications audio au format MP3 dans leur église. Cette API est construite avec Laravel 10 et utilise l'authentification Sanctum.

## Fonctionnalités

-   **Création de sermons** : Publier des prédications avec fichier audio
-   **Gestion des sermons** : Voir, modifier et supprimer ses sermons
-   **Upload de fichiers** : Support des fichiers audio MP3 et images de couverture en base64
-   **Autorisation** : Seuls les propriétaires d'église peuvent publier des sermons
-   **Relations** : Chaque sermon appartient à une église

## Structure des données

### Modèle Sermon

| Champ         | Type      | Description                  | Obligatoire |
| ------------- | --------- | ---------------------------- | ----------- |
| id            | integer   | Identifiant unique           | Auto        |
| title         | string    | Titre du sermon              | ✓           |
| preacher_name | string    | Nom du prédicateur           | ✓           |
| description   | text      | Description du sermon        | ✗           |
| audio_url     | string    | URL du fichier audio         | ✓           |
| cover_url     | string    | URL de l'image de couverture | ✗           |
| duration      | integer   | Durée en secondes            | ✗           |
| church_id     | integer   | ID de l'église               | ✓           |
| created_at    | timestamp | Date de création             | Auto        |
| updated_at    | timestamp | Date de modification         | Auto        |

### Attributs calculés

-   `formatted_duration` : Durée formatée (ex: "25:30")

## Endpoints API

### Base URL

```
/api/sermons
```

### Authentification

Tous les endpoints nécessitent une authentification via Sanctum.

```
Authorization: Bearer {your-token}
```

### 1. Lister tous les sermons

```http
GET /api/sermons
```

**Réponse :**

```json
{
    "data": [
        {
            "id": 1,
            "title": "La grâce de Dieu",
            "preacher_name": "Pasteur Jean MUKENDI",
            "description": "Description du sermon...",
            "audio_url": "http://localhost/storage/sermons/audio/sample.mp3",
            "cover_url": "http://localhost/storage/sermons/covers/cover.jpg",
            "duration": 1800,
            "formatted_duration": "30:00",
            "church_id": 1,
            "created_at": "2025-10-02T10:00:00.000000Z",
            "updated_at": "2025-10-02T10:00:00.000000Z"
        }
    ]
}
```

### 2. Voir les sermons de mon église

```http
GET /api/sermons/my-church-sermons
```

Retourne uniquement les sermons de l'église appartenant à l'utilisateur connecté.

### 3. Créer un nouveau sermon

```http
POST /api/sermons
```

**Body :**

```json
{
    "title": "Titre du sermon",
    "preacher_name": "Nom du prédicateur",
    "description": "Description optionnelle",
    "audio_base64": "data:audio/mp3;base64,/9j/4AAQSkZJRgABAQEA...",
    "cover_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA...",
    "duration": 1800,
    "church_id": 1
}
```

**Validation :**

-   `title` : requis, string, max 255 caractères
-   `preacher_name` : requis, string, max 255 caractères
-   `description` : optionnel, string
-   `audio_base64` : requis, format base64 audio valide
-   `cover_base64` : optionnel, format base64 image valide
-   `duration` : optionnel, entier >= 1
-   `church_id` : requis, doit exister et appartenir à l'utilisateur

### 4. Voir un sermon spécifique

```http
GET /api/sermons/{id}
```

### 5. Modifier un sermon

```http
PUT /api/sermons/{id}
PATCH /api/sermons/{id}
```

**Body :** Mêmes champs que la création, mais tous optionnels avec `sometimes|required`

### 6. Supprimer un sermon

```http
DELETE /api/sermons/{id}
```

## Gestion des fichiers

### Formats supportés

**Audio :**

-   MP3 (audio/mp3)
-   WAV (audio/wav)
-   M4A (audio/mp4)

**Images de couverture :**

-   JPEG (image/jpeg)
-   PNG (image/png)
-   WEBP (image/webp)

### Upload Base64

Les fichiers sont envoyés au format base64 avec le header approprié :

```javascript
// Exemple pour audio MP3
"audio_base64": "data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA..."

// Exemple pour image JPEG
"cover_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/..."
```

### Stockage

Les fichiers sont stockés dans :

-   Audio : `storage/app/public/sermons/audio/`
-   Couvertures : `storage/app/public/sermons/covers/`

## Sécurité et autorisations

### Règles d'autorisation

1. **Création de sermon** : L'utilisateur doit posséder une église
2. **Modification/Suppression** : Seul le propriétaire de l'église peut modifier/supprimer ses sermons
3. **Consultation** : Tous les utilisateurs authentifiés peuvent voir tous les sermons

### Validation des fichiers

-   Vérification du format base64
-   Validation du type MIME
-   Taille maximale des fichiers (configurable)
-   Noms de fichiers uniques générés automatiquement

## Exemples d'utilisation

### Création d'un sermon avec JavaScript

```javascript
const formData = {
    title: "La foi qui déplace les montagnes",
    preacher_name: "Pasteur Marie KALALA",
    description: "Un message sur la puissance de la foi...",
    audio_base64: audioBase64String,
    cover_base64: imageBase64String,
    duration: 2100,
    church_id: 1,
};

fetch("/api/sermons", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(formData),
})
    .then((response) => response.json())
    .then((data) => console.log(data));
```

### Récupération des sermons de son église

```javascript
fetch("/api/sermons/my-church-sermons", {
    headers: {
        Authorization: `Bearer ${token}`,
    },
})
    .then((response) => response.json())
    .then((data) => {
        console.log("Mes sermons:", data.data);
    });
```

## Codes d'erreur

| Code | Description                              |
| ---- | ---------------------------------------- |
| 200  | Succès                                   |
| 201  | Créé avec succès                         |
| 400  | Erreur de validation                     |
| 401  | Non authentifié                          |
| 403  | Non autorisé (pas propriétaire d'église) |
| 404  | Sermon non trouvé                        |
| 422  | Données invalides                        |
| 500  | Erreur serveur                           |

## Tests

Des tests automatisés couvrent :

-   Création de sermons avec upload audio
-   Récupération des sermons par église
-   Validation des données d'entrée
-   Autorisations et sécurité

Lancer les tests :

```bash
php artisan test tests/Feature/SermonControllerTest.php
```

## Relations avec l'API Church

L'API Sermon étend l'API Church existante :

-   Chaque sermon appartient à une église (`church_id`)
-   Seuls les propriétaires d'église peuvent créer des sermons
-   La suppression d'une église supprime tous ses sermons associés

Pour plus d'informations sur l'API Church, voir `CHURCH_CONTROLLER.md`.
