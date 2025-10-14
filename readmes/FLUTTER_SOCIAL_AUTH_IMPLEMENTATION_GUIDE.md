# Guide d'Implémentation de l'Authentification Sociale dans Flutter

Ce guide vous montre comment implémenter l'authentification sociale (Google et Facebook) dans votre application Flutter en utilisant l'API Laravel backend.

## Table des Matières
1. [Architecture et Flux](#architecture-et-flux)
2. [Configuration Préalable](#configuration-préalable)
3. [Installation des Packages](#installation-des-packages)
4. [Configuration Android](#configuration-android)
5. [Configuration iOS](#configuration-ios)
6. [Implémentation du Code](#implémentation-du-code)
7. [Tests et Débogage](#tests-et-débogage)

---

## Architecture et Flux

### Flux d'Authentification
```
1. Utilisateur clique sur "Se connecter avec Google/Facebook"
2. Flutter ouvre la fenêtre de connexion du provider
3. L'utilisateur s'authentifie
4. Flutter reçoit l'access_token du provider
5. Flutter envoie l'access_token à l'API Laravel
6. Laravel vérifie le token et crée/récupère l'utilisateur
7. Laravel retourne un token Sanctum
8. Flutter stocke le token et redirige l'utilisateur
```

---

## Configuration Préalable

### 1. Google Cloud Console
1. Aller sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créer un nouveau projet ou sélectionner un existant
3. Activer **Google Sign-In API**
4. Créer des identifiants OAuth 2.0 :
   - **Android** : OAuth 2.0 Client ID (type Android)
   - **iOS** : OAuth 2.0 Client ID (type iOS)
   - **Web** : OAuth 2.0 Client ID (type Web) - pour le backend

### 2. Facebook Developer Console
1. Aller sur [Facebook Developers](https://developers.facebook.com/)
2. Créer une nouvelle application
3. Ajouter le produit **Facebook Login**
4. Configurer les paramètres OAuth :
   - Ajouter les URLs de redirection
   - Activer le login client OAuth
5. Noter l'**App ID** et l'**App Secret**

---

## Installation des Packages

Ajouter les packages suivants dans `pubspec.yaml` :

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP Client
  http: ^1.1.0
  
  # State Management (Choisir un)
  provider: ^6.1.1
  # ou
  # flutter_bloc: ^8.1.3
  # ou
  # riverpod: ^2.4.9
  
  # Authentification Sociale
  google_sign_in: ^6.1.5
  flutter_facebook_auth: ^6.0.3
  
  # Stockage Local
  shared_preferences: ^2.2.2
  flutter_secure_storage: ^9.0.0
  
  # Gestion des Tokens
  jwt_decoder: ^2.0.1

dev_dependencies:
  flutter_test:
    sdk: flutter
```

Puis exécuter :
```bash
flutter pub get
```

---

## Configuration Android

### 1. Modifier `android/app/build.gradle`

```gradle
android {
    compileSdkVersion 34  // Minimum 21
    
    defaultConfig {
        applicationId "com.votreapp.neno"  // Votre package name
        minSdkVersion 21
        targetSdkVersion 34
    }
}

dependencies {
    // ... autres dépendances
}
```

### 2. Configurer Google Sign-In

Dans `android/app/src/main/AndroidManifest.xml` :

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <application>
        <!-- ... -->
        
        <!-- Google Sign-In -->
        <meta-data
            android:name="com.google.android.gms.version"
            android:value="@integer/google_play_services_version" />
    </application>
    
    <uses-permission android:name="android.permission.INTERNET"/>
</manifest>
```

### 3. Configurer Facebook Login

Dans `android/app/src/main/AndroidManifest.xml` :

```xml
<application>
    <!-- Facebook Configuration -->
    <meta-data 
        android:name="com.facebook.sdk.ApplicationId" 
        android:value="@string/facebook_app_id"/>
    
    <meta-data 
        android:name="com.facebook.sdk.ClientToken" 
        android:value="@string/facebook_client_token"/>
        
    <activity 
        android:name="com.facebook.FacebookActivity"
        android:configChanges="keyboard|keyboardHidden|screenLayout|screenSize|orientation"
        android:label="@string/app_name" />
        
    <activity
        android:name="com.facebook.CustomTabActivity"
        android:exported="true">
        <intent-filter>
            <action android:name="android.intent.action.VIEW" />
            <category android:name="android.intent.category.DEFAULT" />
            <category android:name="android.intent.category.BROWSABLE" />
            <data android:scheme="@string/fb_login_protocol_scheme" />
        </intent-filter>
    </activity>
</application>
```

Créer `android/app/src/main/res/values/strings.xml` :

```xml
<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string name="app_name">Neno</string>
    <string name="facebook_app_id">VOTRE_FACEBOOK_APP_ID</string>
    <string name="fb_login_protocol_scheme">fbVOTRE_FACEBOOK_APP_ID</string>
    <string name="facebook_client_token">VOTRE_FACEBOOK_CLIENT_TOKEN</string>
</resources>
```

---

## Configuration iOS

### 1. Modifier `ios/Runner/Info.plist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <!-- ... -->
    
    <!-- Google Sign-In -->
    <key>CFBundleURLTypes</key>
    <array>
        <dict>
            <key>CFBundleTypeRole</key>
            <string>Editor</string>
            <key>CFBundleURLSchemes</key>
            <array>
                <string>com.googleusercontent.apps.VOTRE-CLIENT-ID</string>
            </array>
        </dict>
        <!-- Facebook -->
        <dict>
            <key>CFBundleURLSchemes</key>
            <array>
                <string>fbVOTRE_FACEBOOK_APP_ID</string>
            </array>
        </dict>
    </array>
    
    <!-- Facebook Configuration -->
    <key>FacebookAppID</key>
    <string>VOTRE_FACEBOOK_APP_ID</string>
    <key>FacebookClientToken</key>
    <string>VOTRE_FACEBOOK_CLIENT_TOKEN</string>
    <key>FacebookDisplayName</key>
    <string>Neno</string>
    
    <key>LSApplicationQueriesSchemes</key>
    <array>
        <string>fbapi</string>
        <string>fb-messenger-share-api</string>
        <string>fbauth2</string>
        <string>fbshareextension</string>
    </array>
</dict>
</plist>
```

### 2. Augmenter la version minimum iOS

Dans `ios/Podfile` :

```ruby
platform :ios, '12.0'
```

---

## Implémentation du Code

### 1. Créer le Modèle Utilisateur

`lib/models/user_model.dart` :

```dart
class UserModel {
  final int id;
  final String name;
  final String email;
  final String? emailVerifiedAt;
  final String roleType;
  final String createdAt;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.emailVerifiedAt,
    required this.roleType,
    required this.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      emailVerifiedAt: json['email_verified_at'],
      roleType: json['role_type'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'email_verified_at': emailVerifiedAt,
      'role_type': roleType,
      'created_at': createdAt,
    };
  }
}
```

### 2. Créer le Modèle de Réponse d'Authentification

`lib/models/auth_response_model.dart` :

```dart
import 'user_model.dart';

class AuthResponse {
  final bool success;
  final String message;
  final UserModel? user;
  final String? token;
  final String? tokenType;

  AuthResponse({
    required this.success,
    required this.message,
    this.user,
    this.token,
    this.tokenType,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      success: json['success'],
      message: json['message'],
      user: json['data'] != null && json['data']['user'] != null
          ? UserModel.fromJson(json['data']['user'])
          : null,
      token: json['data']?['token'],
      tokenType: json['data']?['token_type'],
    );
  }
}
```

### 3. Créer le Service d'API

`lib/services/api_service.dart` :

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/auth_response_model.dart';

class ApiService {
  // IMPORTANT: Remplacer par votre URL API
  static const String baseUrl = 'https://votre-api.com/api';
  
  // Pour le développement local Android Emulator
  // static const String baseUrl = 'http://10.0.2.2:8000/api';
  
  // Pour le développement local iOS Simulator
  // static const String baseUrl = 'http://localhost:8000/api';

  /// Authentification avec un provider social
  Future<AuthResponse> socialLogin({
    required String provider, // 'google' ou 'facebook'
    required String accessToken,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/social-login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'provider': provider,
          'access_token': accessToken,
        }),
      );

      final data = jsonDecode(response.body);
      return AuthResponse.fromJson(data);
    } catch (e) {
      return AuthResponse(
        success: false,
        message: 'Erreur de connexion: ${e.toString()}',
      );
    }
  }

  /// Lier un compte social à un utilisateur authentifié
  Future<AuthResponse> linkSocialAccount({
    required String provider,
    required String accessToken,
    required String userToken,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/social-link'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $userToken',
        },
        body: jsonEncode({
          'provider': provider,
          'access_token': accessToken,
        }),
      );

      final data = jsonDecode(response.body);
      return AuthResponse.fromJson(data);
    } catch (e) {
      return AuthResponse(
        success: false,
        message: 'Erreur de liaison: ${e.toString()}',
      );
    }
  }

  /// Délier un compte social
  Future<AuthResponse> unlinkSocialAccount({
    required String provider,
    required String userToken,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/social-unlink'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $userToken',
        },
        body: jsonEncode({
          'provider': provider,
        }),
      );

      final data = jsonDecode(response.body);
      return AuthResponse.fromJson(data);
    } catch (e) {
      return AuthResponse(
        success: false,
        message: 'Erreur de déliaison: ${e.toString()}',
      );
    }
  }

  /// Obtenir le statut des comptes sociaux
  Future<Map<String, bool>> getSocialAccountsStatus(String userToken) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/auth/social-status'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $userToken',
        },
      );

      final data = jsonDecode(response.body);
      if (data['success']) {
        return {
          'google_linked': data['data']['google_linked'] ?? false,
          'facebook_linked': data['data']['facebook_linked'] ?? false,
        };
      }
      return {'google_linked': false, 'facebook_linked': false};
    } catch (e) {
      return {'google_linked': false, 'facebook_linked': false};
    }
  }
}
```

### 4. Créer le Service d'Authentification Sociale

`lib/services/social_auth_service.dart` :

```dart
import 'package:google_sign_in/google_sign_in.dart';
import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';

class SocialAuthService {
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  /// Connexion avec Google
  Future<String?> signInWithGoogle() async {
    try {
      // Se déconnecter d'abord pour forcer la sélection de compte
      await _googleSignIn.signOut();
      
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      
      if (googleUser == null) {
        return null; // L'utilisateur a annulé
      }

      final GoogleSignInAuthentication googleAuth = 
          await googleUser.authentication;

      return googleAuth.accessToken;
    } catch (error) {
      print('Erreur Google Sign-In: $error');
      return null;
    }
  }

  /// Déconnexion Google
  Future<void> signOutGoogle() async {
    await _googleSignIn.signOut();
  }

  /// Connexion avec Facebook
  Future<String?> signInWithFacebook() async {
    try {
      // Se déconnecter d'abord
      await FacebookAuth.instance.logOut();
      
      final LoginResult result = await FacebookAuth.instance.login(
        permissions: ['email', 'public_profile'],
      );

      if (result.status == LoginStatus.success) {
        return result.accessToken?.token;
      } else if (result.status == LoginStatus.cancelled) {
        print('Connexion Facebook annulée');
        return null;
      } else {
        print('Erreur Facebook: ${result.message}');
        return null;
      }
    } catch (error) {
      print('Erreur Facebook Sign-In: $error');
      return null;
    }
  }

  /// Déconnexion Facebook
  Future<void> signOutFacebook() async {
    await FacebookAuth.instance.logOut();
  }

  /// Déconnexion complète
  Future<void> signOutAll() async {
    await signOutGoogle();
    await signOutFacebook();
  }
}
```

### 5. Créer le Service de Stockage

`lib/services/storage_service.dart` :

```dart
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/user_model.dart';

class StorageService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  /// Sauvegarder le token d'authentification (sécurisé)
  Future<void> saveToken(String token) async {
    await _secureStorage.write(key: _tokenKey, value: token);
  }

  /// Récupérer le token d'authentification
  Future<String?> getToken() async {
    return await _secureStorage.read(key: _tokenKey);
  }

  /// Supprimer le token
  Future<void> deleteToken() async {
    await _secureStorage.delete(key: _tokenKey);
  }

  /// Sauvegarder les données utilisateur
  Future<void> saveUser(UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userKey, jsonEncode(user.toJson()));
  }

  /// Récupérer les données utilisateur
  Future<UserModel?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString(_userKey);
    
    if (userJson != null) {
      return UserModel.fromJson(jsonDecode(userJson));
    }
    return null;
  }

  /// Supprimer les données utilisateur
  Future<void> deleteUser() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_userKey);
  }

  /// Vérifier si l'utilisateur est connecté
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Déconnexion complète
  Future<void> clearAll() async {
    await deleteToken();
    await deleteUser();
  }
}
```

### 6. Créer le Provider d'Authentification (avec Provider package)

`lib/providers/auth_provider.dart` :

```dart
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/social_auth_service.dart';
import '../services/storage_service.dart';

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  final SocialAuthService _socialAuthService = SocialAuthService();
  final StorageService _storageService = StorageService();

  UserModel? _user;
  String? _token;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _user != null && _token != null;

  /// Initialiser l'état d'authentification
  Future<void> initialize() async {
    _isLoading = true;
    notifyListeners();

    _token = await _storageService.getToken();
    _user = await _storageService.getUser();

    _isLoading = false;
    notifyListeners();
  }

  /// Connexion avec Google
  Future<bool> signInWithGoogle() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Obtenir le token Google
      final accessToken = await _socialAuthService.signInWithGoogle();
      
      if (accessToken == null) {
        _errorMessage = 'Connexion Google annulée';
        _isLoading = false;
        notifyListeners();
        return false;
      }

      // Authentifier avec l'API
      final response = await _apiService.socialLogin(
        provider: 'google',
        accessToken: accessToken,
      );

      if (response.success && response.user != null && response.token != null) {
        _user = response.user;
        _token = response.token;
        
        // Sauvegarder localement
        await _storageService.saveToken(response.token!);
        await _storageService.saveUser(response.user!);
        
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.message;
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Erreur: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Connexion avec Facebook
  Future<bool> signInWithFacebook() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Obtenir le token Facebook
      final accessToken = await _socialAuthService.signInWithFacebook();
      
      if (accessToken == null) {
        _errorMessage = 'Connexion Facebook annulée';
        _isLoading = false;
        notifyListeners();
        return false;
      }

      // Authentifier avec l'API
      final response = await _apiService.socialLogin(
        provider: 'facebook',
        accessToken: accessToken,
      );

      if (response.success && response.user != null && response.token != null) {
        _user = response.user;
        _token = response.token;
        
        // Sauvegarder localement
        await _storageService.saveToken(response.token!);
        await _storageService.saveUser(response.user!);
        
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.message;
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Erreur: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Lier un compte Google
  Future<bool> linkGoogleAccount() async {
    if (_token == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final accessToken = await _socialAuthService.signInWithGoogle();
      
      if (accessToken == null) {
        _errorMessage = 'Liaison Google annulée';
        _isLoading = false;
        notifyListeners();
        return false;
      }

      final response = await _apiService.linkSocialAccount(
        provider: 'google',
        accessToken: accessToken,
        userToken: _token!,
      );

      if (response.success && response.user != null) {
        _user = response.user;
        await _storageService.saveUser(response.user!);
        
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.message;
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Erreur: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Lier un compte Facebook
  Future<bool> linkFacebookAccount() async {
    if (_token == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final accessToken = await _socialAuthService.signInWithFacebook();
      
      if (accessToken == null) {
        _errorMessage = 'Liaison Facebook annulée';
        _isLoading = false;
        notifyListeners();
        return false;
      }

      final response = await _apiService.linkSocialAccount(
        provider: 'facebook',
        accessToken: accessToken,
        userToken: _token!,
      );

      if (response.success && response.user != null) {
        _user = response.user;
        await _storageService.saveUser(response.user!);
        
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.message;
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Erreur: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Déconnexion
  Future<void> signOut() async {
    await _socialAuthService.signOutAll();
    await _storageService.clearAll();
    
    _user = null;
    _token = null;
    _errorMessage = null;
    
    notifyListeners();
  }
}
```

### 7. Créer l'Interface de Connexion

`lib/screens/login_screen.dart` :

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends StatelessWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Logo ou Titre
              const Icon(
                Icons.church,
                size: 80,
                color: Colors.blue,
              ),
              const SizedBox(height: 16),
              const Text(
                'Neno App',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Connectez-vous pour continuer',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey,
                ),
              ),
              const SizedBox(height: 48),

              // Bouton Google
              Consumer<AuthProvider>(
                builder: (context, authProvider, child) {
                  return ElevatedButton.icon(
                    onPressed: authProvider.isLoading
                        ? null
                        : () async {
                            final success = await authProvider.signInWithGoogle();
                            if (success && context.mounted) {
                              Navigator.pushReplacementNamed(context, '/home');
                            } else if (authProvider.errorMessage != null && context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(authProvider.errorMessage!),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          },
                    icon: Image.asset(
                      'assets/images/google_logo.png',
                      height: 24,
                      width: 24,
                    ),
                    label: const Text('Se connecter avec Google'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: Colors.black87,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                        side: const BorderSide(color: Colors.grey),
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 16),

              // Bouton Facebook
              Consumer<AuthProvider>(
                builder: (context, authProvider, child) {
                  return ElevatedButton.icon(
                    onPressed: authProvider.isLoading
                        ? null
                        : () async {
                            final success = await authProvider.signInWithFacebook();
                            if (success && context.mounted) {
                              Navigator.pushReplacementNamed(context, '/home');
                            } else if (authProvider.errorMessage != null && context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(authProvider.errorMessage!),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          },
                    icon: const Icon(Icons.facebook, color: Colors.white),
                    label: const Text('Se connecter avec Facebook'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1877F2),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  );
                },
              ),

              const SizedBox(height: 24),

              // Indicateur de chargement
              Consumer<AuthProvider>(
                builder: (context, authProvider, child) {
                  if (authProvider.isLoading) {
                    return const Center(
                      child: CircularProgressIndicator(),
                    );
                  }
                  return const SizedBox.shrink();
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 8. Créer l'Écran d'Accueil

`lib/screens/home_screen.dart` :

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Accueil'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await authProvider.signOut();
              if (context.mounted) {
                Navigator.pushReplacementNamed(context, '/login');
              }
            },
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Bienvenue, ${user?.name ?? "Utilisateur"} !',
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: ListTile(
                leading: const Icon(Icons.email),
                title: const Text('Email'),
                subtitle: Text(user?.email ?? 'N/A'),
              ),
            ),
            Card(
              child: ListTile(
                leading: const Icon(Icons.person),
                title: const Text('Rôle'),
                subtitle: Text(user?.roleType ?? 'N/A'),
              ),
            ),
            Card(
              child: ListTile(
                leading: const Icon(Icons.calendar_today),
                title: const Text('Membre depuis'),
                subtitle: Text(user?.createdAt ?? 'N/A'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

### 9. Configurer main.dart

`lib/main.dart` :

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider(),
      child: MaterialApp(
        title: 'Neno App',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          primarySwatch: Colors.blue,
          useMaterial3: true,
        ),
        home: const SplashScreen(),
        routes: {
          '/login': (context) => const LoginScreen(),
          '/home': (context) => const HomeScreen(),
        },
      ),
    );
  }
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({Key? key}) : super(key: key);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAuthStatus();
  }

  Future<void> _checkAuthStatus() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.initialize();

    if (mounted) {
      if (authProvider.isAuthenticated) {
        Navigator.pushReplacementNamed(context, '/home');
      } else {
        Navigator.pushReplacementNamed(context, '/login');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: CircularProgressIndicator(),
      ),
    );
  }
}
```

---

## Tests et Débogage

### 1. Tester sur Android Emulator

```bash
# Lancer l'émulateur
flutter emulators --launch <emulator_id>

# Exécuter l'application
flutter run
```

### 2. Tester sur iOS Simulator

```bash
# Ouvrir le simulateur
open -a Simulator

# Exécuter l'application
flutter run
```

### 3. Logs de Débogage

Pour voir les logs détaillés :

```bash
flutter run --verbose
```

### 4. Problèmes Courants

#### Google Sign-In ne fonctionne pas
- Vérifier que le SHA-1 est correctement configuré dans Google Console
- S'assurer que l'OAuth Client ID est correct
- Vérifier les permissions dans AndroidManifest.xml

#### Facebook Login ne fonctionne pas
- Vérifier l'App ID et Client Token
- S'assurer que l'application est en mode "Live" dans Facebook Console
- Vérifier les URL schemes dans Info.plist (iOS) et AndroidManifest.xml

#### Erreur de connexion à l'API
- Vérifier l'URL de l'API dans `ApiService`
- Pour Android Emulator : utiliser `10.0.2.2` au lieu de `localhost`
- S'assurer que le backend Laravel est en cours d'exécution

### 5. Commandes Utiles

```bash
# Nettoyer le build
flutter clean

# Récupérer les dépendances
flutter pub get

# Analyser le code
flutter analyze

# Formater le code
flutter format .

# Générer l'APK Android
flutter build apk --release

# Générer l'iOS IPA
flutter build ios --release
```

---

## Sécurité

### Bonnes Pratiques

1. **Stocker le token de manière sécurisée** : Utiliser `flutter_secure_storage`
2. **Ne jamais logger les tokens** : Éviter de print() les tokens en production
3. **Valider les certificats SSL** : Toujours utiliser HTTPS en production
4. **Gérer l'expiration des tokens** : Implémenter un refresh token système
5. **Nettoyer les données sensibles** : Supprimer les tokens lors de la déconnexion

### Configuration Production

Avant de publier :

1. Désactiver le mode debug
2. Utiliser les URLs de production
3. Activer ProGuard (Android)
4. Configurer le code signing (iOS)
5. Tester sur des appareils réels

---

## Ressources Supplémentaires

- [Documentation Google Sign-In Flutter](https://pub.dev/packages/google_sign_in)
- [Documentation Facebook Login Flutter](https://pub.dev/packages/flutter_facebook_auth)
- [Documentation Laravel Socialite](https://laravel.com/docs/socialite)
- [Documentation Provider](https://pub.dev/packages/provider)

---

## Support

Pour toute question ou problème :
1. Vérifier les logs de l'application Flutter
2. Vérifier les logs du backend Laravel
3. Consulter la documentation des packages utilisés
4. Tester les endpoints API avec Postman

---

**Note** : Remplacez tous les `VOTRE_XXX` par vos vraies valeurs avant de déployer !

