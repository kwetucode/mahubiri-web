# Authentification Sociale - Configuration Rapide

## 🚀 Quick Start

### 1. Variables .env

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback
```

### 2. API Endpoints disponibles

#### Connexion sociale (Public)

```
POST /api/auth/social/login
Body: {
  "provider": "google|facebook",
  "access_token": "..."
}
```

#### Lier un compte (Authentifié)

```
POST /api/user/social/link
Headers: Authorization: Bearer {token}
Body: {
  "provider": "google|facebook",
  "access_token": "..."
}
```

#### Délier un compte (Authentifié)

```
POST /api/user/social/unlink
Headers: Authorization: Bearer {token}
Body: {
  "provider": "google|facebook"
}
```

#### Statut des comptes sociaux (Authentifié)

```
GET /api/user/social/status
Headers: Authorization: Bearer {token}
```

### 3. Flutter - Installation

```yaml
dependencies:
    google_sign_in: ^6.1.5
    flutter_facebook_auth: ^6.0.3
    http: ^1.1.0
    flutter_secure_storage: ^9.0.0
```

### 4. Flutter - Exemple de code

```dart
// Connexion avec Google
final GoogleSignIn _googleSignIn = GoogleSignIn(scopes: ['email']);

Future<void> signInWithGoogle() async {
  final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
  final GoogleSignInAuthentication googleAuth = await googleUser!.authentication;

  final response = await http.post(
    Uri.parse('$baseUrl/auth/social/login'),
    body: jsonEncode({
      'provider': 'google',
      'access_token': googleAuth.accessToken,
    }),
  );

  // Sauvegarder le token retourné
}

// Connexion avec Facebook
Future<void> signInWithFacebook() async {
  final LoginResult result = await FacebookAuth.instance.login();
  final accessToken = result.accessToken!.tokenString;

  final response = await http.post(
    Uri.parse('$baseUrl/auth/social/login'),
    body: jsonEncode({
      'provider': 'facebook',
      'access_token': accessToken,
    }),
  );

  // Sauvegarder le token retourné
}
```

## 📖 Documentation complète

Consultez [SOCIAL_AUTH_FLUTTER_GUIDE.md](./SOCIAL_AUTH_FLUTTER_GUIDE.md) pour la documentation complète avec :

-   Configuration détaillée Android/iOS
-   Exemples de code complets
-   Gestion des erreurs
-   Débogage
-   Flux d'authentification

## 🔧 Fonctionnalités

-   ✅ Connexion avec Google
-   ✅ Connexion avec Facebook
-   ✅ Liaison de comptes sociaux à un compte existant
-   ✅ Déliaison de comptes sociaux
-   ✅ Vérification automatique d'email pour les comptes sociaux
-   ✅ Création automatique de compte si l'utilisateur n'existe pas
-   ✅ Authentification via tokens Sanctum

## 📝 Structure de la base de données

La table `users` a été étendue avec les colonnes :

-   `google_id` (nullable, unique)
-   `google_token` (nullable, text)
-   `facebook_id` (nullable, unique)
-   `facebook_token` (nullable, text)

## ⚠️ Important

1. Ne stockez JAMAIS les tokens sociaux côté Flutter pour longtemps
2. Utilisez le token Sanctum pour toutes les requêtes API après connexion
3. Les comptes créés via social login ont `email_verified_at` automatiquement défini
