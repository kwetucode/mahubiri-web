# Tests d'Authentification Laravel

Ce répertoire contient une suite complète de tests pour toutes les fonctionnalités d'authentification de l'application.

## Structure des Tests

### Tests Feature (Tests d'Intégration)

-   **RegisterControllerTest.php** - Tests du contrôleur d'inscription
-   **LoginControllerTest.php** - Tests du contrôleur de connexion
-   **EmailVerificationControllerTest.php** - Tests de vérification d'email
-   **AuthenticationIntegrationTest.php** - Tests d'intégration complète

### Tests Unit (Tests Unitaires)

-   **LoginRequestTest.php** - Tests de validation de la LoginRequest
-   **RegisterRequestTest.php** - Tests de validation de la RegisterRequest
-   **UserResourceTest.php** - Tests de transformation UserResource
-   **RoleResourceTest.php** - Tests de transformation RoleResource

## Commandes pour Exécuter les Tests

### Tous les tests d'authentification

```bash
php artisan test tests/Feature/Auth tests/Unit/Requests tests/Unit/Resources
```

### Tests spécifiques

#### Tests des contrôleurs

```bash
php artisan test tests/Feature/Auth/RegisterControllerTest.php
php artisan test tests/Feature/Auth/LoginControllerTest.php
php artisan test tests/Feature/Auth/EmailVerificationControllerTest.php
```

#### Tests d'intégration

```bash
php artisan test tests/Feature/Auth/AuthenticationIntegrationTest.php
```

#### Tests des Form Requests

```bash
php artisan test tests/Unit/Requests/LoginRequestTest.php
php artisan test tests/Unit/Requests/RegisterRequestTest.php
```

#### Tests des Resources

```bash
php artisan test tests/Unit/Resources/UserResourceTest.php
php artisan test tests/Unit/Resources/RoleResourceTest.php
```

### Tests avec couverture

```bash
php artisan test --coverage
```

### Tests en mode verbose

```bash
php artisan test --verbose
```

## Fonctionnalités Testées

### ✅ RegisterController

-   Inscription réussie avec données valides
-   Inscription avec rôle personnalisé
-   Validation des champs obligatoires
-   Validation format email
-   Validation unicité email
-   Validation longueur mot de passe
-   Validation confirmation mot de passe
-   Validation rôle existant
-   Génération de token
-   Structure UserResource

### ✅ LoginController

-   Connexion avec email
-   Connexion avec téléphone
-   Échec avec identifiants invalides
-   Échec avec email non vérifié
-   Validation champs obligatoires
-   Récupération profil utilisateur
-   Déconnexion
-   Sécurité données sensibles
-   Génération tokens multiples

### ✅ EmailVerificationController

-   Envoi email de vérification
-   Échec si déjà vérifié
-   Vérification email réussie
-   Vérification statut
-   Gestion utilisateurs non authentifiés
-   Structure UserResource
-   Événements déclenchés

### ✅ Form Requests

-   **LoginRequest**: Validation email/téléphone, messages personnalisés
-   **RegisterRequest**: Validation complète, messages français, attributs

### ✅ Resources

-   **UserResource**: Transformation, relations, sécurité
-   **RoleResource**: Transformation, types de données, collections

### ✅ Tests d'Intégration

-   Flux complet inscription → connexion
-   Flux vérification email
-   Authentification multi-rôles
-   Cohérence réponses API
-   Mesures de sécurité

## Couverture des Tests

Les tests couvrent :

-   ✅ 100% des endpoints d'authentification
-   ✅ 100% des cas de validation
-   ✅ 100% des cas d'erreur
-   ✅ 100% des transformations de données
-   ✅ Sécurité et protection des données
-   ✅ Intégration complète du système

## Configuration Requise

Assurez-vous que les éléments suivants sont configurés :

-   Base de données de test (SQLite recommandé)
-   Sanctum pour l'authentification API
-   Factories pour User et Role
-   Configuration email pour les tests

## Notes Importantes

-   Les tests utilisent `RefreshDatabase` pour isoler chaque test
-   Les notifications sont mockées avec `Notification::fake()`
-   Les événements sont mockés avec `Event::fake()`
-   Tous les tests sont indépendants et peuvent être exécutés séparément
