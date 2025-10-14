# 🚨 Guide d'utilisation - ApiExceptionHandler

## 📋 Vue d'ensemble

La classe `ApiExceptionHandler` fournit un système centralisé et standardisé pour gérer toutes les exceptions dans votre application Laravel. Elle garantit une cohérence dans les réponses d'erreur et offre un système de logging complet pour le monitoring et le débogage.

## 📁 Localisation

```
app/Exceptions/ApiExceptionHandler.php
```

## 🎯 Objectifs

-   ✅ **Standardisation** : Réponses JSON cohérentes pour toutes les erreurs
-   ✅ **Logging complet** : Traçabilité détaillée de chaque exception
-   ✅ **Séparation des préoccupations** : Messages utilisateur vs messages techniques
-   ✅ **Réutilisabilité** : Utilisable dans toute l'application
-   ✅ **Monitoring** : Logs adaptés au niveau de criticité

## 📊 Structure de réponse

Toutes les méthodes retournent une réponse JSON standardisée :

```json
{
    "success": false,
    "message": "Message convivial pour l'utilisateur",
    "error": "Message technique de l'exception"
}
```

-   **`success`** : Toujours `false` pour les erreurs
-   **`message`** : Message clair et compréhensible pour l'utilisateur final
-   **`error`** : Message technique de l'exception pour le débogage

## 🛠️ Méthodes disponibles

### 🤖 **Méthode intelligente - `auto()` (RECOMMANDÉE)**

```php
ApiExceptionHandler::auto(
    Exception $exception,
    string $operation = 'opération',
    array $additionalData = []
): JsonResponse
```

**✨ Fonctionnalité principale :** Détecte automatiquement le type d'exception et route vers la méthode appropriée.

**Détection automatique :**

-   **ValidationException** → `handleValidationError()`
-   **QueryException** → `handleDatabaseError()`
-   **ModelNotFoundException** → `handleNotFoundError()`
-   **AuthenticationException** → `handleAuthenticationError()`
-   **AuthorizationException** → `handleAuthorizationError()`
-   **FileException** → `handleFileError()`
-   **RequestException** → `handleExternalServiceError()`
-   **BusinessLogicException** → `handleBusinessLogicError()`
-   **Toute autre Exception** → `handleServerError()`

**Exemples d'utilisation :**

```php
// Dans n'importe quel contrôleur
catch (\Exception $e) {
    return ApiExceptionHandler::auto($e, 'création utilisateur', [
        'email' => $request->input('email')
    ]);
}

// Gestion automatique selon le type d'exception:
// - Validation échouée → Message de validation
// - Utilisateur non trouvé → Message "ressource non trouvée"
// - Erreur de BDD → Message d'erreur base de données
// - Etc.
```

### 1. **Méthode principale - `handle()`**

```php
ApiExceptionHandler::handle(
    Exception $exception,
    string $context,
    ?string $customMessage = null,
    int $statusCode = 500,
    array $additionalData = []
): JsonResponse
```

**Utilisation :**

```php
return ApiExceptionHandler::handle(
    $exception,
    'Création utilisateur',
    'Erreur lors de la création du compte',
    500,
    ['user_data' => $userData]
);
```

### 2. **Erreurs serveur (5xx) - `handleServerError()`**

```php
ApiExceptionHandler::handleServerError(
    Exception $exception,
    string $operation = 'opération',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Erreur de connexion
catch (\Exception $e) {
    return ApiExceptionHandler::handleServerError($e, 'connexion utilisateur');
}

// Erreur de création
catch (\Exception $e) {
    return ApiExceptionHandler::handleServerError($e, 'création du compte', [
        'user_email' => $request->email
    ]);
}
```

### 3. **Erreurs de validation (422) - `handleValidationError()`**

```php
ApiExceptionHandler::handleValidationError(
    Exception $exception,
    ?string $customMessage = null,
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Validation générale
catch (\Exception $e) {
    return ApiExceptionHandler::handleValidationError($e);
}

// Message personnalisé
catch (\Exception $e) {
    return ApiExceptionHandler::handleValidationError(
        $e,
        'L\'email est déjà utilisé par un autre compte'
    );
}
```

### 4. **Erreurs d'autorisation (403) - `handleAuthorizationError()`**

```php
ApiExceptionHandler::handleAuthorizationError(
    Exception $exception,
    ?string $customMessage = null,
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Accès refusé
catch (\Exception $e) {
    return ApiExceptionHandler::handleAuthorizationError(
        $e,
        'Vous n\'avez pas les droits pour modifier cette église'
    );
}
```

### 5. **Erreurs d'authentification (401) - `handleAuthenticationError()`**

```php
ApiExceptionHandler::handleAuthenticationError(
    Exception $exception,
    ?string $customMessage = null,
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Échec de connexion
catch (\Exception $e) {
    return ApiExceptionHandler::handleAuthenticationError(
        $e,
        'Identifiants incorrects'
    );
}
```

### 6. **Ressource non trouvée (404) - `handleNotFoundError()`**

```php
ApiExceptionHandler::handleNotFoundError(
    Exception $exception,
    string $resourceName = 'ressource',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Utilisateur non trouvé
catch (\Exception $e) {
    return ApiExceptionHandler::handleNotFoundError($e, 'utilisateur', [
        'id' => $userId
    ]);
}

// Église non trouvée
catch (\Exception $e) {
    return ApiExceptionHandler::handleNotFoundError($e, 'église', [
        'id' => $churchId
    ]);
}
```

### 7. **Erreurs de base de données (500) - `handleDatabaseError()`**

```php
ApiExceptionHandler::handleDatabaseError(
    Exception $exception,
    string $operation = 'opération de base de données',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Erreur d'insertion
catch (\Exception $e) {
    return ApiExceptionHandler::handleDatabaseError(
        $e,
        'insertion du sermon',
        ['sermon_data' => $sermonData]
    );
}
```

### 8. **Erreurs de fichier (500) - `handleFileError()`**

```php
ApiExceptionHandler::handleFileError(
    Exception $exception,
    string $operation = 'opération sur le fichier',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Erreur d'upload
catch (\Exception $e) {
    return ApiExceptionHandler::handleFileError($e, 'téléchargement du sermon', [
        'file_path' => $filePath,
        'file_size' => $fileSize,
        'file_type' => $fileType
    ]);
}
```

### 9. **Erreurs de service externe (502) - `handleExternalServiceError()`**

```php
ApiExceptionHandler::handleExternalServiceError(
    Exception $exception,
    string $serviceName = 'service externe',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// API externe
catch (\Exception $e) {
    return ApiExceptionHandler::handleExternalServiceError(
        $e,
        'service de notification email',
        [
            'url' => $apiUrl,
            'response_code' => $responseCode
        ]
    );
}
```

### 10. **Erreurs de logique métier (400) - `handleBusinessLogicError()`**

```php
ApiExceptionHandler::handleBusinessLogicError(
    Exception $exception,
    string $businessRule = 'règle métier',
    array $additionalData = []
): JsonResponse
```

**Exemples :**

```php
// Règle business violée
catch (\Exception $e) {
    return ApiExceptionHandler::handleBusinessLogicError(
        $e,
        'Un utilisateur ne peut pas être membre de plus de 3 églises'
    );
}
```

## 📋 Exemples concrets par contrôleur

### **LoginController**

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiExceptionHandler;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            // Logique de connexion...

        } catch (\Exception $e) {
            return ApiExceptionHandler::handleServerError($e, 'connexion utilisateur', [
                'login_field' => $request->getLoginField(),
                'login_value' => $request->input('login')
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Logique de déconnexion...

        } catch (\Exception $e) {
            return ApiExceptionHandler::handleServerError($e, 'déconnexion utilisateur');
        }
    }
}
```

### **UserController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiExceptionHandler;

class UserController extends Controller
{
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json(['success' => true, 'data' => $user]);

        } catch (ModelNotFoundException $e) {
            return ApiExceptionHandler::handleNotFoundError($e, 'utilisateur', ['id' => $id]);

        } catch (\Exception $e) {
            return ApiExceptionHandler::handleServerError($e, 'récupération utilisateur');
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            // Création utilisateur...

        } catch (ValidationException $e) {
            return ApiExceptionHandler::handleValidationError($e, 'Données utilisateur invalides');

        } catch (QueryException $e) {
            return ApiExceptionHandler::handleDatabaseError($e, 'création utilisateur');

        } catch (\Exception $e) {
            return ApiExceptionHandler::handleServerError($e, 'création utilisateur');
        }
    }
}
```

### **ChurchController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiExceptionHandler;

class ChurchController extends Controller
{
    public function update(Request $request, $id)
    {
        try {
            $church = Church::findOrFail($id);

            // Vérification des permissions
            if (!$request->user()->canEdit($church)) {
                throw new AuthorizationException();
            }

            // Mise à jour...

        } catch (ModelNotFoundException $e) {
            return ApiExceptionHandler::handleNotFoundError($e, 'église', ['id' => $id]);

        } catch (AuthorizationException $e) {
            return ApiExceptionHandler::handleAuthorizationError(
                $e,
                'Vous n\'avez pas les droits pour modifier cette église'
            );

        } catch (\Exception $e) {
            return ApiExceptionHandler::handleServerError($e, 'mise à jour église');
        }
    }
}
```

## 📊 Système de Logging

### **Niveaux de log automatiques**

-   **🔴 Critical** : Erreurs serveur (5xx)
-   **🟡 Warning** : Erreurs client (4xx)
-   **🔵 Info** : Autres cas

### **Informations loggées**

#### **Logs généraux (méthode `handle()`) :**

-   Classe de l'exception
-   Message et code d'erreur
-   Fichier et ligne de l'erreur
-   Stack trace complète
-   Contexte de la requête (URL, méthode, IP)
-   ID de requête unique
-   Timestamp ISO

#### **Logs spécialisés :**

**Server Error :**

-   Type d'opération
-   Type d'exception
-   Données additionnelles

**Validation Error :**

-   Message personnalisé
-   Données de validation
-   Données de la requête

**Authorization Error :**

-   ID et email utilisateur
-   Action tentée
-   Rôle utilisateur

**Database Error :**

-   Opération de base de données
-   Type d'exception SQL
-   Connexion utilisée

**File Error :**

-   Type d'opération fichier
-   Informations du fichier
-   Espace disque disponible

## 🔧 Configuration

### **Prérequis**

Assurez-vous d'avoir les imports suivants dans vos contrôleurs :

```php
use App\Exceptions\ApiExceptionHandler;
```

### **Configuration des logs**

Dans `config/logging.php`, vous pouvez configurer les channels de log :

```php
'channels' => [
    'exceptions' => [
        'driver' => 'single',
        'path' => storage_path('logs/exceptions.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

## 🚀 Bonnes pratiques

### **1. Utilisez le bon type d'exception**

```php
// ✅ Bon
catch (ValidationException $e) {
    return ApiExceptionHandler::handleValidationError($e, 'Email déjà utilisé');
}

// ❌ Évitez
catch (\Exception $e) {
    return ApiExceptionHandler::handle($e, 'Error', 'Something went wrong');
}
```

### **2. Fournissez un contexte utile**

```php
// ✅ Bon
return ApiExceptionHandler::handleDatabaseError($e, 'création du sermon', [
    'sermon_title' => $request->title,
    'church_id' => $request->church_id,
    'file_size' => $request->file('audio')->getSize()
]);

// ❌ Évitez
return ApiExceptionHandler::handleDatabaseError($e);
```

### **3. Messages utilisateur clairs**

```php
// ✅ Bon
return ApiExceptionHandler::handleValidationError(
    $e,
    'Le fichier audio doit être au format MP3 et faire moins de 100MB'
);

// ❌ Évitez
return ApiExceptionHandler::handleValidationError($e, 'Validation failed');
```

### **4. Gestion en cascade**

```php
public function uploadSermon(Request $request)
{
    try {
        // Logique principale...

    } catch (ValidationException $e) {
        return ApiExceptionHandler::handleValidationError($e, 'Données du sermon invalides');

    } catch (FileException $e) {
        return ApiExceptionHandler::handleFileError($e, 'téléchargement du fichier audio');

    } catch (QueryException $e) {
        return ApiExceptionHandler::handleDatabaseError($e, 'sauvegarde du sermon');

    } catch (\Exception $e) {
        return ApiExceptionHandler::handleServerError($e, 'création du sermon');
    }
}
```

## 📈 Monitoring et debugging

### **Consultation des logs**

```bash
# Logs généraux
tail -f storage/logs/laravel.log

# Filtrer les erreurs critiques
grep "Critical Error" storage/logs/laravel.log

# Filtrer par contexte
grep "Database Error" storage/logs/laravel.log
```

### **Métriques à surveiller**

-   Fréquence des erreurs 5xx vs 4xx
-   Types d'exceptions les plus fréquentes
-   Temps de réponse des erreurs
-   Erreurs par endpoint

## 🔄 Évolutions futures

### **Extensions possibles**

1. **Notification d'erreurs critiques** (Slack, email)
2. **Métriques de performance** (temps de réponse)
3. **Rate limiting** sur les erreurs répétées
4. **Intégration avec des services de monitoring** (Sentry, Bugsnag)
5. **Retry automatique** pour certains types d'erreurs

---

## 📞 Support

Pour toute question ou amélioration sur le système d'exceptions, consultez la documentation Laravel ou contactez l'équipe de développement.

**Version :** 1.0  
**Dernière mise à jour :** Octobre 2025
