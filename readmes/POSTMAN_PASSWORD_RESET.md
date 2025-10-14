# Tests Postman - Réinitialisation de Mot de Passe

## Collection Postman

Importez cette collection dans Postman pour tester l'API de réinitialisation de mot de passe.

### Variables d'environnement

Créez un environnement Postman avec ces variables :

-   `base_url` : `http://192.168.235.97:8002/api/v1`
-   `test_email` : `user@example.com`

---

## 1. Demander un lien de réinitialisation

### Request

**POST** `{{base_url}}/auth/password/email`

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**

```json
{
    "email": "{{test_email}}"
}
```

### Test Cases

#### ✅ Succès - Email valide

```json
// Body
{
    "email": "user@example.com"
}

// Expected Response (200)
{
    "success": true,
    "message": "Password reset link sent to your email"
}
```

#### ❌ Email manquant

```json
// Body
{}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": [
            "The email field is required."
        ]
    }
}
```

#### ❌ Email invalide (format)

```json
// Body
{
    "email": "invalid-email"
}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": [
            "The email field must be a valid email address."
        ]
    }
}
```

#### ❌ Email inexistant

```json
// Body
{
    "email": "nonexistent@example.com"
}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": [
            "The selected email is invalid."
        ]
    }
}
```

---

## 2. Réinitialiser le mot de passe

### Request

**POST** `{{base_url}}/auth/password/reset`

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**

```json
{
    "token": "OBTENU_PAR_EMAIL",
    "email": "{{test_email}}",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
```

### Test Cases

#### ✅ Succès - Token et données valides

```json
// Body
{
    "token": "abc123def456...",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}

// Expected Response (200)
{
    "success": true,
    "message": "Password reset successfully"
}
```

#### ❌ Token manquant

```json
// Body
{
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "token": [
            "The token field is required."
        ]
    }
}
```

#### ❌ Mot de passe trop court

```json
// Body
{
    "token": "abc123",
    "email": "user@example.com",
    "password": "short",
    "password_confirmation": "short"
}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "password": [
            "The password field must be at least 8 characters."
        ]
    }
}
```

#### ❌ Mots de passe ne correspondent pas

```json
// Body
{
    "token": "abc123",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "DifferentPassword123!"
}

// Expected Response (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "password": [
            "The password field confirmation does not match."
        ]
    }
}
```

#### ❌ Token invalide ou expiré

```json
// Body
{
    "token": "invalid_token",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}

// Expected Response (400)
{
    "success": false,
    "message": "Invalid token or email"
}
```

#### ❌ Email ne correspond pas au token

```json
// Body
{
    "token": "valid_token_for_another_user",
    "email": "wrong@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}

// Expected Response (400)
{
    "success": false,
    "message": "Invalid token or email"
}
```

---

## Workflow de test complet

### 1. Préparation

1. Créer un utilisateur de test si nécessaire (via l'endpoint de registration)
2. Configurer la variable `test_email` avec l'email de test
3. Configurer Mailtrap ou un serveur email de test

### 2. Test du flux complet

**Étape 1 : Demander la réinitialisation**

```bash
POST {{base_url}}/auth/password/email
Body: { "email": "user@example.com" }
Expected: 200 OK
```

**Étape 2 : Récupérer le token**

-   Ouvrir Mailtrap ou votre boîte email
-   Copier le token de réinitialisation depuis l'email
-   Le token est visible dans le corps de l'email

**Étape 3 : Réinitialiser le mot de passe**

```bash
POST {{base_url}}/auth/password/reset
Body: {
    "token": "TOKEN_FROM_EMAIL",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
Expected: 200 OK
```

**Étape 4 : Vérifier la connexion**

```bash
POST {{base_url}}/auth/login
Body: {
    "email": "user@example.com",
    "password": "NewPassword123!"
}
Expected: 200 OK avec token d'authentification
```

---

## Collection JSON pour import Postman

Sauvegardez ce JSON dans un fichier `.json` et importez-le dans Postman :

```json
{
    "info": {
        "name": "Password Reset API",
        "description": "API de réinitialisation de mot de passe",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "1. Request Password Reset",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"email\": \"{{test_email}}\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/auth/password/email",
                    "host": ["{{base_url}}"],
                    "path": ["auth", "password", "email"]
                },
                "description": "Demander un lien de réinitialisation de mot de passe"
            }
        },
        {
            "name": "2. Reset Password",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"token\": \"PASTE_TOKEN_FROM_EMAIL\",\n    \"email\": \"{{test_email}}\",\n    \"password\": \"NewPassword123!\",\n    \"password_confirmation\": \"NewPassword123!\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/auth/password/reset",
                    "host": ["{{base_url}}"],
                    "path": ["auth", "password", "reset"]
                },
                "description": "Réinitialiser le mot de passe avec le token reçu par email"
            }
        },
        {
            "name": "3. Login with New Password",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"email\": \"{{test_email}}\",\n    \"password\": \"NewPassword123!\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/auth/login",
                    "host": ["{{base_url}}"],
                    "path": ["auth", "login"]
                },
                "description": "Tester la connexion avec le nouveau mot de passe"
            }
        }
    ],
    "variable": [
        {
            "key": "base_url",
            "value": "http://192.168.235.97:8002/api/v1"
        },
        {
            "key": "test_email",
            "value": "user@example.com"
        }
    ]
}
```

---

## Scripts de test automatisés (Tests Postman)

### Pour "Request Password Reset"

**Tests Tab:**

```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property("success");
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has message", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property("message");
});

pm.test("Message confirms email sent", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include("email");
});
```

### Pour "Reset Password"

**Tests Tab:**

```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Password reset successful", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.message).to.include("successfully");
});
```

---

## Commandes cURL

### Demander la réinitialisation

```bash
curl -X POST http://192.168.235.97:8002/api/v1/auth/password/email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"user@example.com"}'
```

### Réinitialiser le mot de passe

```bash
curl -X POST http://192.168.235.97:8002/api/v1/auth/password/reset \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "token":"TOKEN_FROM_EMAIL",
    "email":"user@example.com",
    "password":"NewPassword123!",
    "password_confirmation":"NewPassword123!"
  }'
```

---

## Troubleshooting

### Erreur : "Route [password.reset] not defined"

✅ **Solution** : Cette erreur est maintenant corrigée. La route est définie avec le nom `password.reset` dans `routes/api.php`.

### Email non reçu

-   Vérifiez les logs : `storage/logs/laravel.log`
-   Vérifiez la configuration email dans `.env`
-   Vérifiez Mailtrap ou votre serveur SMTP

### Token invalide après quelques minutes

-   Les tokens expirent après 60 minutes par défaut
-   Demandez un nouveau token

### Tests échouent avec 419

-   Assurez-vous d'inclure le header `Accept: application/json`
-   Les routes API ne nécessitent pas de token CSRF
