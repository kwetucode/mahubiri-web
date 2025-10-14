# Guide d'Authentification Sociale - API Laravel pour Flutter

## 📋 Table des matières

1. [Configuration Backend (Laravel)](#configuration-backend)
2. [Configuration Flutter](#configuration-flutter)
3. [API Endpoints](#api-endpoints)
4. [Exemples d'utilisation Flutter](#exemples-flutter)
5. [Flux d'authentification](#flux-authentification)

---

## 🔧 Configuration Backend (Laravel)

### 1. Variables d'environnement

Ajoutez les variables suivantes dans votre fichier `.env` :

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback
```

### 2. Obtenir les credentials

#### Pour Google:

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet ou sélectionnez un projet existant
3. Activez l'API Google+
4. Créez des identifiants OAuth 2.0
5. Pour Flutter, ajoutez également un client OAuth pour Android/iOS

#### Pour Facebook:

1. Allez sur [Facebook Developers](https://developers.facebook.com/)
2. Créez une nouvelle application
3. Ajoutez le produit "Facebook Login"
4. Configurez les paramètres OAuth
5. Récupérez l'App ID et l'App Secret

---

## 📱 Configuration Flutter

### 1. Dépendances Flutter

Ajoutez ces packages dans votre `pubspec.yaml` :

```yaml
dependencies:
    flutter:
        sdk: flutter
    http: ^1.1.0
    google_sign_in: ^6.1.5
    flutter_facebook_auth: ^6.0.3
    flutter_secure_storage: ^9.0.0
```

### 2. Configuration Android (Google Sign-In)

**android/app/build.gradle** :

```gradle
dependencies {
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}
```

### 3. Configuration iOS (Google Sign-In)

**ios/Runner/Info.plist** :

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>com.googleusercontent.apps.YOUR-CLIENT-ID</string>
        </array>
    </dict>
</array>
```

### 4. Configuration Facebook

**Android (android/app/src/main/res/values/strings.xml)** :

```xml
<resources>
    <string name="app_name">Your App Name</string>
    <string name="facebook_app_id">YOUR_FACEBOOK_APP_ID</string>
    <string name="fb_login_protocol_scheme">fbYOUR_FACEBOOK_APP_ID</string>
    <string name="facebook_client_token">YOUR_CLIENT_TOKEN</string>
</resources>
```

**Android (android/app/src/main/AndroidManifest.xml)** :

```xml
<meta-data android:name="com.facebook.sdk.ApplicationId" android:value="@string/facebook_app_id"/>
<meta-data android:name="com.facebook.sdk.ClientToken" android:value="@string/facebook_client_token"/>
```

**iOS (ios/Runner/Info.plist)** :

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>fbYOUR_FACEBOOK_APP_ID</string>
        </array>
    </dict>
</array>
<key>FacebookAppID</key>
<string>YOUR_FACEBOOK_APP_ID</string>
<key>FacebookClientToken</key>
<string>YOUR_CLIENT_TOKEN</string>
<key>FacebookDisplayName</key>
<string>Your App Name</string>
```

---

## 🔌 API Endpoints

### 1. Connexion sociale (Public)

**POST** `/api/auth/social/login`

Authentifie un utilisateur avec un fournisseur social (Google ou Facebook).

**Request Body:**

```json
{
    "provider": "google",
    "access_token": "ya29.a0AfH6SMBx..."
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Successfully authenticated with Google",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": "2025-10-11T10:30:00.000000Z",
            "role_type": "member",
            "created_at": "2025-10-11T10:30:00.000000Z"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

**Response (400) - Erreur:**

```json
{
    "success": false,
    "message": "Unable to retrieve user information from Google"
}
```

---

### 2. Lier un compte social (Protégé)

**POST** `/api/user/social/link`

Lie un compte social à un utilisateur authentifié.

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:**

```json
{
    "provider": "facebook",
    "access_token": "EAABxxxxxxx..."
}
```

**Response (200):**

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

### 3. Délier un compte social (Protégé)

**POST** `/api/user/social/unlink`

Délie un compte social d'un utilisateur authentifié.

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:**

```json
{
    "provider": "google"
}
```

**Response (200):**

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

---

### 4. Statut des comptes sociaux (Protégé)

**GET** `/api/user/social/status`

Récupère le statut des comptes sociaux liés.

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "google_linked": true,
        "facebook_linked": false
    }
}
```

---

## 💻 Exemples d'utilisation Flutter

### Service d'authentification

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';

class AuthService {
  static const String baseUrl = 'http://your-api-url.com/api';
  final storage = const FlutterSecureStorage();

  // Google Sign-In
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  // Connexion avec Google
  Future<Map<String, dynamic>> signInWithGoogle() async {
    try {
      // 1. Authentification Google côté client
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        throw Exception('Google sign in aborted');
      }

      // 2. Récupérer les credentials
      final GoogleSignInAuthentication googleAuth =
          await googleUser.authentication;

      // 3. Envoyer le token à votre API
      final response = await http.post(
        Uri.parse('$baseUrl/auth/social/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'provider': 'google',
          'access_token': googleAuth.accessToken,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        // 4. Sauvegarder le token
        await storage.write(
          key: 'auth_token',
          value: data['data']['token'],
        );

        return data;
      } else {
        throw Exception('Failed to authenticate with backend');
      }
    } catch (e) {
      throw Exception('Google sign in failed: $e');
    }
  }

  // Connexion avec Facebook
  Future<Map<String, dynamic>> signInWithFacebook() async {
    try {
      // 1. Authentification Facebook côté client
      final LoginResult result = await FacebookAuth.instance.login(
        permissions: ['email', 'public_profile'],
      );

      if (result.status != LoginStatus.success) {
        throw Exception('Facebook sign in failed');
      }

      // 2. Récupérer le token d'accès
      final accessToken = result.accessToken!.tokenString;

      // 3. Envoyer le token à votre API
      final response = await http.post(
        Uri.parse('$baseUrl/auth/social/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'provider': 'facebook',
          'access_token': accessToken,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        // 4. Sauvegarder le token
        await storage.write(
          key: 'auth_token',
          value: data['data']['token'],
        );

        return data;
      } else {
        throw Exception('Failed to authenticate with backend');
      }
    } catch (e) {
      throw Exception('Facebook sign in failed: $e');
    }
  }

  // Lier un compte Google
  Future<Map<String, dynamic>> linkGoogleAccount() async {
    try {
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        throw Exception('Google sign in aborted');
      }

      final GoogleSignInAuthentication googleAuth =
          await googleUser.authentication;

      final token = await storage.read(key: 'auth_token');

      final response = await http.post(
        Uri.parse('$baseUrl/user/social/link'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'provider': 'google',
          'access_token': googleAuth.accessToken,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to link Google account');
      }
    } catch (e) {
      throw Exception('Link Google account failed: $e');
    }
  }

  // Délier un compte social
  Future<Map<String, dynamic>> unlinkSocialAccount(String provider) async {
    try {
      final token = await storage.read(key: 'auth_token');

      final response = await http.post(
        Uri.parse('$baseUrl/user/social/unlink'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'provider': provider,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to unlink $provider account');
      }
    } catch (e) {
      throw Exception('Unlink account failed: $e');
    }
  }

  // Vérifier le statut des comptes sociaux
  Future<Map<String, dynamic>> getSocialAccountsStatus() async {
    try {
      final token = await storage.read(key: 'auth_token');

      final response = await http.get(
        Uri.parse('$baseUrl/user/social/status'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to get social accounts status');
      }
    } catch (e) {
      throw Exception('Get status failed: $e');
    }
  }

  // Déconnexion
  Future<void> signOut() async {
    await _googleSignIn.signOut();
    await FacebookAuth.instance.logOut();
    await storage.delete(key: 'auth_token');
  }
}
```

### Exemple d'utilisation dans un Widget

```dart
import 'package:flutter/material.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final AuthService _authService = AuthService();
  bool _isLoading = false;

  Future<void> _handleGoogleSignIn() async {
    setState(() => _isLoading = true);

    try {
      final result = await _authService.signInWithGoogle();

      // Navigation vers l'écran principal
      Navigator.pushReplacementNamed(context, '/home');

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Connexion réussie!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _handleFacebookSignIn() async {
    setState(() => _isLoading = true);

    try {
      final result = await _authService.signInWithFacebook();

      // Navigation vers l'écran principal
      Navigator.pushReplacementNamed(context, '/home');

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Connexion réussie!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Connexion')),
      body: Center(
        child: _isLoading
            ? CircularProgressIndicator()
            : Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ElevatedButton.icon(
                    onPressed: _handleGoogleSignIn,
                    icon: Icon(Icons.login),
                    label: Text('Se connecter avec Google'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: Colors.black,
                      padding: EdgeInsets.symmetric(
                        horizontal: 24,
                        vertical: 12,
                      ),
                    ),
                  ),
                  SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: _handleFacebookSignIn,
                    icon: Icon(Icons.facebook),
                    label: Text('Se connecter avec Facebook'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Color(0xFF1877F2),
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(
                        horizontal: 24,
                        vertical: 12,
                      ),
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
```

---

## 🔄 Flux d'authentification

### Flux de connexion

```
1. Utilisateur clique sur "Se connecter avec Google/Facebook"
   ↓
2. L'application Flutter lance le processus d'authentification social
   ↓
3. L'utilisateur se connecte avec son compte Google/Facebook
   ↓
4. Flutter récupère l'access_token du fournisseur
   ↓
5. Flutter envoie l'access_token à votre API Laravel
   ↓
6. Laravel vérifie le token avec le fournisseur
   ↓
7. Laravel trouve ou crée l'utilisateur
   ↓
8. Laravel génère un token Sanctum
   ↓
9. Flutter reçoit et stocke le token Sanctum
   ↓
10. L'utilisateur est connecté!
```

### Flux de liaison de compte

```
1. Utilisateur déjà connecté clique sur "Lier compte Google/Facebook"
   ↓
2. Même processus d'authentification social
   ↓
3. Flutter envoie access_token + token Sanctum à l'API
   ↓
4. Laravel lie le compte social au compte existant
   ↓
5. Utilisateur peut maintenant se connecter avec les deux méthodes
```

---

## ⚠️ Points importants

1. **Sécurité** : Ne stockez JAMAIS les tokens sociaux côté client pour une longue durée
2. **Token Sanctum** : Utilisez toujours le token Sanctum pour les requêtes API après la connexion
3. **Expiration** : Les tokens sociaux expirent, rafraîchissez-les si nécessaire
4. **Email vérifié** : Les comptes créés via social login sont automatiquement vérifiés
5. **Permissions** : Demandez uniquement les permissions nécessaires (email, profile)

---

## 🐛 Débogage

### Tester l'API avec Postman/cURL

```bash
# Test connexion Google
curl -X POST http://your-api-url.com/api/auth/social/login \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "google",
    "access_token": "ya29.a0AfH6SMBx..."
  }'

# Test lier compte (avec token Sanctum)
curl -X POST http://your-api-url.com/api/user/social/link \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxxxx" \
  -d '{
    "provider": "facebook",
    "access_token": "EAABxxxxxxx..."
  }'
```

---

## 📚 Ressources

-   [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
-   [Google Sign-In Flutter](https://pub.dev/packages/google_sign_in)
-   [Flutter Facebook Auth](https://pub.dev/packages/flutter_facebook_auth)
-   [Laravel Sanctum](https://laravel.com/docs/sanctum)

---

**Créé le** : 11 Octobre 2025  
**Version** : 1.0.0
