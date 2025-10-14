# 🏛️ Church API Documentation

## Vue d'ensemble

L'API Church permet aux utilisateurs authentifiés de gérer des églises dans l'application. Chaque utilisateur ne peut créer qu'une seule église, garantissant l'unicité et la responsabilité.

## 📋 Table des matières

-   [Modèle de données](#modèle-de-données)
-   [Endpoints API](#endpoints-api)
-   [Authentification](#authentification)
-   [Validation](#validation)
-   [Gestion des images](#gestion-des-images)
-   [Règles métier](#règles-métier)
-   [Exemples d'utilisation](#exemples-dutilisation)
-   [Tests](#tests)
-   [Structure des fichiers](#structure-des-fichiers)

## 🗄️ Modèle de données

### Table `churches`

| Champ          | Type        | Description                  | Requis |
| -------------- | ----------- | ---------------------------- | ------ |
| `id`           | bigint      | Identifiant unique           | Auto   |
| `name`         | string(255) | Nom de l'église              | ✅     |
| `abbreviation` | string(10)  | Abréviation de l'église      | ❌     |
| `logo_url`     | text        | URL du logo de l'église      | ❌     |
| `description`  | text        | Description de l'église      | ❌     |
| `created_by`   | bigint      | ID de l'utilisateur créateur | ✅     |
| `created_at`   | timestamp   | Date de création             | Auto   |
| `updated_at`   | timestamp   | Date de modification         | Auto   |

### Relations

-   **created_by** → `users.id` (Foreign Key avec suppression en cascade)
-   **Contrainte unique** : Un utilisateur ne peut créer qu'une seule église

## 🔗 Endpoints API

Toutes les routes sont protégées par l'authentification Sanctum et préfixées par `/api/churches`.

### 1. Lister toutes les églises

```http
GET /api/churches
```

**Réponse :**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Église Saint-Pierre",
            "abbreviation": "ESP",
            "logo_url": "http://localhost/storage/church_logos/abc123.jpg",
            "description": "Une belle église...",
            "created_by": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "created_at": "2025-10-02 14:30:00",
            "updated_at": "2025-10-02 14:30:00"
        }
    ],
    "message": "Churches retrieved successfully"
}
```

### 2. Créer une nouvelle église

```http
POST /api/churches
```

**Corps de la requête :**

```json
{
    "name": "Église Saint-Pierre",
    "abbreviation": "ESP",
    "description": "Description de l'église...",
    "logo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ..."
}
```

**Réponse (201) :**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Église Saint-Pierre",
        "abbreviation": "ESP",
        "logo_url": "http://localhost/storage/church_logos/xyz123.jpg",
        "description": "Description de l'église...",
        "created_by": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-10-02 14:30:00",
        "updated_at": "2025-10-02 14:30:00"
    },
    "message": "Church created successfully"
}
```

### 3. Voir une église spécifique

```http
GET /api/churches/{id}
```

**Réponse :**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Église Saint-Pierre"
        // ... autres champs
    },
    "message": "Church retrieved successfully"
}
```

### 4. Mettre à jour une église

```http
PUT /api/churches/{id}
PATCH /api/churches/{id}
```

**Corps de la requête :**

```json
{
    "name": "Nouveau nom d'église",
    "abbreviation": "NNE",
    "description": "Nouvelle description...",
    "logo": "data:image/png;base64,iVBORw0KGgoAAAANSUhEU..."
}
```

### 5. Supprimer une église

```http
DELETE /api/churches/{id}
```

**Réponse :**

```json
{
    "success": true,
    "message": "Church deleted successfully"
}
```

### 6. Vérifier l'église de l'utilisateur authentifié

```http
GET /api/churches/my-church
```

**Réponse (avec église) :**

```json
{
    "success": true,
    "has_church": true,
    "data": {
        "id": 1,
        "name": "Mon Église"
        // ... autres champs
    },
    "message": "Utilisateur a déjà une église"
}
```

**Réponse (sans église) :**

```json
{
    "success": true,
    "has_church": false,
    "data": null,
    "message": "Utilisateur n'a pas encore d'église"
}
```

## 🔐 Authentification

Toutes les routes nécessitent une authentification via Laravel Sanctum :

```http
Authorization: Bearer {token}
```

## ✅ Validation

### Règles de validation (Création)

-   **name** : Requis, string, max 255 caractères
-   **abbreviation** : Optionnel, string, max 10 caractères
-   **description** : Optionnel, string
-   **logo** : Optionnel, format base64 valide (`data:image/{type};base64,{data}`)

### Règles de validation (Mise à jour)

-   **name** : Parfois requis, string, max 255 caractères
-   **abbreviation** : Optionnel, string, max 10 caractères
-   **description** : Optionnel, string
-   **logo** : Optionnel, format base64 valide

### Messages d'erreur personnalisés

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["Le nom de l'église est requis."],
        "abbreviation": ["L'abréviation ne peut pas dépasser 10 caractères."],
        "logo": [
            "Le format de l'image logo n'est pas valide (doit être en base64)."
        ]
    }
}
```

## 🖼️ Gestion des images

### Upload via Base64

L'API accepte les images au format base64 depuis les applications Flutter :

```json
{
    "logo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

### Traitement des images

1. **Décodage** : L'image base64 est décodée
2. **Validation** : Vérification du format (image/jpeg, image/png, etc.)
3. **Stockage** : Sauvegarde dans `storage/app/public/church_logos/`
4. **URL** : Génération de l'URL publique accessible
5. **Nettoyage** : Suppression automatique des anciens logos lors des mises à jour

### Structure de stockage

```
storage/app/public/
└── church_logos/
    ├── abc123def456.jpg
    ├── xyz789ghi012.png
    └── ...
```

## 📜 Règles métier

### Unicité par utilisateur

-   ✅ Chaque utilisateur ne peut créer qu'**une seule église**
-   ✅ Contrainte unique au niveau base de données
-   ✅ Validation au niveau Request et Controller
-   ✅ Messages d'erreur explicites

### Gestion des propriétés

-   ✅ L'utilisateur qui crée l'église devient automatiquement son propriétaire
-   ✅ Le champ `created_by` est automatiquement rempli avec l'ID de l'utilisateur authentifié

### Suppression en cascade

-   ✅ Si un utilisateur est supprimé, son église est automatiquement supprimée
-   ✅ Les logos associés sont également supprimés du stockage

## 💻 Exemples d'utilisation

### Création d'une église avec Flutter

```dart
// Dart/Flutter
final response = await http.post(
  Uri.parse('http://localhost:8000/api/churches'),
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer $token',
  },
  body: jsonEncode({
    'name': 'Ma Nouvelle Église',
    'abbreviation': 'MNE',
    'description': 'Une description...',
    'logo': 'data:image/jpeg;base64,$base64Image',
  }),
);
```

### Vérification avant création

```dart
// Vérifier si l'utilisateur a déjà une église
final checkResponse = await http.get(
  Uri.parse('http://localhost:8000/api/churches/my-church'),
  headers: {'Authorization': 'Bearer $token'},
);

final data = jsonDecode(checkResponse.body);
if (data['has_church']) {
  // L'utilisateur a déjà une église
  showDialog(context: context, builder: (_) => AlertDialog(
    title: Text('Information'),
    content: Text('Vous avez déjà créé une église.'),
  ));
} else {
  // Permettre la création
  navigateToCreateChurch();
}
```

## 🧪 Tests

### Tests Feature (ChurchController)

```bash
php artisan test --filter=ChurchControllerTest
```

**Tests couverts :**

-   ✅ Liste des églises
-   ✅ Création d'église
-   ✅ Protection contre créations multiples
-   ✅ Vérification du statut utilisateur
-   ✅ Validation des champs

### Tests Unit (Validation)

```bash
php artisan test --filter=StoreChurchRequestTest
```

**Tests couverts :**

-   ✅ Règles de validation
-   ✅ Messages d'erreur personnalisés
-   ✅ Champs optionnels et requis
-   ✅ Format base64 des images

### Exécuter tous les tests

```bash
php artisan test tests/Feature/ChurchControllerTest.php
php artisan test tests/Unit/StoreChurchRequestTest.php
```

## 📁 Structure des fichiers

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Church/
│   │       └── ChurchController.php     # Controller principal
│   ├── Requests/
│   │   ├── StoreChurchRequest.php       # Validation création
│   │   └── UpdateChurchRequest.php      # Validation mise à jour
│   └── Resources/
│       └── ChurchResource.php           # Formatage des réponses
├── Models/
│   └── Church.php                       # Modèle Eloquent
database/
├── factories/
│   └── ChurchFactory.php                # Factory pour les tests
└── migrations/
    └── create_churches_table.php        # Migration de la table
routes/
└── api.php                              # Routes API
tests/
├── Feature/
│   └── ChurchControllerTest.php         # Tests d'intégration
└── Unit/
    └── StoreChurchRequestTest.php       # Tests unitaires
storage/app/public/
└── church_logos/                        # Stockage des logos
```

## 🚀 Déployement

### Configuration requise

1. **Lien symbolique pour le stockage :**

```bash
php artisan storage:link
```

2. **Migrations :**

```bash
php artisan migrate
```

3. **Permissions :**

```bash
chmod -R 775 storage/app/public/church_logos/
```

### Variables d'environnement

```env
# Dans .env
FILESYSTEM_DISK=public
APP_URL=http://localhost:8000
```

## 🔧 Configuration avancée

### Personnalisation des tailles d'images

Modifiez la méthode `handleImageUpload()` dans `ChurchController.php` pour ajouter des restrictions :

```php
// Exemple : limite de taille
if (strlen($imageData) > 2 * 1024 * 1024) { // 2MB
    throw new \InvalidArgumentException('Image trop volumineuse');
}
```

### Ajout de nouveaux formats

Modifiez la regex dans les Requests :

```php
'logo' => ['nullable', 'string', 'regex:/^data:image\/(jpeg|jpg|png|gif|webp);base64,/'],
```

## 📞 Support

Pour toute question ou problème :

1. Vérifiez les logs Laravel : `storage/logs/laravel.log`
2. Exécutez les tests pour diagnostiquer : `php artisan test`
3. Vérifiez les permissions de stockage
4. Consultez la documentation Laravel Sanctum pour l'authentification

---

_Documentation générée le 2 octobre 2025_
