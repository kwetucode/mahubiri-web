# Collection Postman - Authentification Sociale

## Configuration

**Base URL**: `http://localhost:8000/api`

---

## 1. Connexion avec Google

### Request

```
POST {{base_url}}/auth/social/login
Content-Type: application/json

{
    "provider": "google",
    "access_token": "ya29.a0AfH6SMBxxxxxxxxxxxxxxxxxxxxx"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Successfully authenticated with Google",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@gmail.com",
            "email_verified_at": "2025-10-11T10:30:00.000000Z",
            "role_type": "member",
            "created_at": "2025-10-11T10:30:00.000000Z"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

### Error Response (400)

```json
{
    "success": false,
    "message": "Unable to retrieve user information from Google"
}
```

### Error Response (422)

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "provider": ["The provider field is required."],
        "access_token": ["The access token field is required."]
    }
}
```

---

## 2. Connexion avec Facebook

### Request

```
POST {{base_url}}/auth/social/login
Content-Type: application/json

{
    "provider": "facebook",
    "access_token": "EAABxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Successfully authenticated with Facebook",
    "data": {
        "user": {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@facebook.com",
            "email_verified_at": "2025-10-11T11:00:00.000000Z",
            "role_type": "member",
            "created_at": "2025-10-11T11:00:00.000000Z"
        },
        "token": "2|yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy",
        "token_type": "Bearer"
    }
}
```

---

## 3. Lier un compte Google (Utilisateur authentifié)

### Request

```
POST {{base_url}}/user/social/link
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "provider": "google",
    "access_token": "ya29.a0AfH6SMBxxxxxxxxxxxxxxxxxxxxx"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Google account linked successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "google_linked": true,
            "facebook_linked": false
        }
    }
}
```

### Error Response (400) - Compte déjà lié

```json
{
    "success": false,
    "message": "This Google account is already linked to another user"
}
```

---

## 4. Lier un compte Facebook (Utilisateur authentifié)

### Request

```
POST {{base_url}}/user/social/link
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "provider": "facebook",
    "access_token": "EAABxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Facebook account linked successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "google_linked": true,
            "facebook_linked": true
        }
    }
}
```

---

## 5. Délier un compte Google

### Request

```
POST {{base_url}}/user/social/unlink
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "provider": "google"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Google account unlinked successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "google_linked": false,
            "facebook_linked": true
        }
    }
}
```

### Error Response (400) - Compte non lié

```json
{
    "success": false,
    "message": "Google account is not linked"
}
```

---

## 6. Délier un compte Facebook

### Request

```
POST {{base_url}}/user/social/unlink
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "provider": "facebook"
}
```

### Success Response (200)

```json
{
    "success": true,
    "message": "Facebook account unlinked successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "google_linked": false,
            "facebook_linked": false
        }
    }
}
```

---

## 7. Statut des comptes sociaux

### Request

```
GET {{base_url}}/user/social/status
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Success Response (200)

```json
{
    "success": true,
    "data": {
        "google_linked": true,
        "facebook_linked": true
    }
}
```

---

## Variables Postman

Configurez ces variables dans votre environnement Postman :

```
base_url = http://localhost:8000/api
token = (sera rempli automatiquement après connexion)
google_token = (votre token Google de test)
facebook_token = (votre token Facebook de test)
```

---

## Tests automatisés (Postman Scripts)

### Script après connexion réussie

```javascript
// Dans l'onglet "Tests" de la requête de connexion
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.data.token);
    pm.test("Token saved successfully", function () {
        pm.expect(pm.environment.get("token")).to.not.be.undefined;
    });
}
```

### Script de validation de la structure

```javascript
pm.test("Response has correct structure", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property("success");
    pm.expect(jsonData).to.have.property("data");
    pm.expect(jsonData.data).to.have.property("user");
    pm.expect(jsonData.data).to.have.property("token");
});
```

---

## Comment obtenir les tokens de test

### Google Token

1. Utilisez [OAuth 2.0 Playground](https://developers.google.com/oauthplayground/)
2. Sélectionnez "Google OAuth2 API v2"
3. Cochez "https://www.googleapis.com/auth/userinfo.email"
4. Cliquez sur "Authorize APIs"
5. Échangez le code d'autorisation contre un token
6. Copiez l'access_token

### Facebook Token

1. Allez sur [Facebook Graph API Explorer](https://developers.facebook.com/tools/explorer/)
2. Sélectionnez votre application
3. Cliquez sur "Get Token" > "Get User Access Token"
4. Sélectionnez les permissions : email, public_profile
5. Copiez le token généré

---

## cURL Examples

### Connexion Google

```bash
curl -X POST http://localhost:8000/api/auth/social/login \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "google",
    "access_token": "ya29.a0AfH6SMBxxxxxxxxxxxxxxxxxxxxx"
  }'
```

### Lier compte Facebook

```bash
curl -X POST http://localhost:8000/api/user/social/link \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -d '{
    "provider": "facebook",
    "access_token": "EAABxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }'
```

### Statut des comptes

```bash
curl -X GET http://localhost:8000/api/user/social/status \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

---

**Note**: Remplacez les tokens d'exemple par vos vrais tokens de test obtenus via les OAuth Playgrounds.
