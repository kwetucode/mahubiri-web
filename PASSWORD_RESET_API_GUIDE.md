# API de Réinitialisation de Mot de Passe

## Vue d'ensemble

Cette API permet aux utilisateurs de Flutter de réinitialiser leur mot de passe en deux étapes :

1. Demander un lien/token de réinitialisation par email
2. Utiliser le token pour définir un nouveau mot de passe

## Configuration

### Variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```env
# URL de votre application Flutter (pour le deep linking)
FRONTEND_URL=myapp://reset-password

# Configuration email (exemple avec Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuration du Deep Linking Flutter

Pour que les liens de réinitialisation ouvrent directement votre app Flutter :

1. **Android** - `android/app/src/main/AndroidManifest.xml` :

```xml
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="myapp" android:host="reset-password" />
</intent-filter>
```

2. **iOS** - `ios/Runner/Info.plist` :

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>myapp</string>
        </array>
    </dict>
</array>
```

## Endpoints

### 1. Demander un lien de réinitialisation

**POST** `/api/v1/auth/password/email`

#### Request Body

```json
{
    "email": "user@example.com"
}
```

#### Validation

-   `email` : requis, format email valide, doit exister dans la base de données

#### Responses

**✅ Success (200)**

```json
{
    "success": true,
    "message": "Password reset link sent to your email"
}
```

**❌ Validation Error (422)**

```json
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

**❌ User Not Found (422)**

```json
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": ["The selected email is invalid."]
    }
}
```

**❌ Server Error (400)**

```json
{
    "success": false,
    "message": "Unable to send password reset link"
}
```

---

### 2. Réinitialiser le mot de passe

**POST** `/api/v1/auth/password/reset`

#### Request Body

```json
{
    "token": "abc123...",
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Validation

-   `token` : requis, string
-   `email` : requis, format email valide
-   `password` : requis, string, minimum 8 caractères
-   `password_confirmation` : requis, doit correspondre au mot de passe

#### Responses

**✅ Success (200)**

```json
{
    "success": true,
    "message": "Password reset successfully"
}
```

**❌ Validation Error (422)**

```json
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "password": ["The password field must be at least 8 characters."],
        "password_confirmation": ["The password confirmation does not match."]
    }
}
```

**❌ Invalid Token (400)**

```json
{
    "success": false,
    "message": "Invalid token or email"
}
```

## Email envoyé

L'utilisateur recevra un email contenant :

-   Le token de réinitialisation en clair (pour le copier manuellement)
-   Un bouton/lien qui ouvre directement l'app Flutter avec le token

**Format du lien** : `myapp://reset-password?token=TOKEN&email=EMAIL`

## Implémentation Flutter

### 1. Demander la réinitialisation

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class PasswordResetService {
  static const String baseUrl = 'http://192.168.235.97:8002/api/v1';

  // Étape 1 : Demander le lien de réinitialisation
  Future<Map<String, dynamic>> requestPasswordReset(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/password/email'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Une erreur est survenue',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur de connexion: $e',
      };
    }
  }

  // Étape 2 : Réinitialiser le mot de passe avec le token
  Future<Map<String, dynamic>> resetPassword({
    required String token,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/password/reset'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'token': token,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Une erreur est survenue',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur de connexion: $e',
      };
    }
  }
}
```

### 2. Interface de demande de réinitialisation

```dart
import 'package:flutter/material.dart';

class ForgotPasswordScreen extends StatefulWidget {
  @override
  _ForgotPasswordScreenState createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordResetService = PasswordResetService();
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _requestPasswordReset() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final result = await _passwordResetService.requestPasswordReset(
      _emailController.text.trim(),
    );

    setState(() => _isLoading = false);

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.green,
        ),
      );

      // Naviguer vers l'écran de saisie du token
      Navigator.pushNamed(
        context,
        '/reset-password',
        arguments: _emailController.text.trim(),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Mot de passe oublié'),
      ),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'Réinitialiser votre mot de passe',
                style: Theme.of(context).textTheme.headlineSmall,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 16),
              Text(
                'Entrez votre email pour recevoir un lien de réinitialisation',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
              SizedBox(height: 32),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer votre email';
                  }
                  if (!value.contains('@')) {
                    return 'Email invalide';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _requestPasswordReset,
                child: _isLoading
                    ? SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : Text('Envoyer le lien'),
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 3. Interface de réinitialisation avec token

```dart
import 'package:flutter/material.dart';

class ResetPasswordScreen extends StatefulWidget {
  final String? email;
  final String? token;

  ResetPasswordScreen({this.email, this.token});

  @override
  _ResetPasswordScreenState createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _tokenController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _passwordResetService = PasswordResetService();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void initState() {
    super.initState();
    if (widget.email != null) {
      _emailController.text = widget.email!;
    }
    if (widget.token != null) {
      _tokenController.text = widget.token!;
    }
  }

  @override
  void dispose() {
    _tokenController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _resetPassword() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final result = await _passwordResetService.resetPassword(
      token: _tokenController.text.trim(),
      email: _emailController.text.trim(),
      password: _passwordController.text,
      passwordConfirmation: _confirmPasswordController.text,
    );

    setState(() => _isLoading = false);

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.green,
        ),
      );

      // Rediriger vers la page de connexion
      Navigator.pushNamedAndRemoveUntil(
        context,
        '/login',
        (route) => false,
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.red,
          duration: Duration(seconds: 5),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Nouveau mot de passe'),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'Créer un nouveau mot de passe',
                style: Theme.of(context).textTheme.headlineSmall,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 32),
              TextFormField(
                controller: _tokenController,
                decoration: InputDecoration(
                  labelText: 'Code de réinitialisation',
                  prefixIcon: Icon(Icons.vpn_key),
                  border: OutlineInputBorder(),
                  helperText: 'Code reçu par email',
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer le code';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer votre email';
                  }
                  if (!value.contains('@')) {
                    return 'Email invalide';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                decoration: InputDecoration(
                  labelText: 'Nouveau mot de passe',
                  prefixIcon: Icon(Icons.lock),
                  border: OutlineInputBorder(),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() => _obscurePassword = !_obscurePassword);
                    },
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer un mot de passe';
                  }
                  if (value.length < 8) {
                    return 'Le mot de passe doit contenir au moins 8 caractères';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _confirmPasswordController,
                obscureText: _obscureConfirmPassword,
                decoration: InputDecoration(
                  labelText: 'Confirmer le mot de passe',
                  prefixIcon: Icon(Icons.lock_outline),
                  border: OutlineInputBorder(),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() => _obscureConfirmPassword = !_obscureConfirmPassword);
                    },
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez confirmer le mot de passe';
                  }
                  if (value != _passwordController.text) {
                    return 'Les mots de passe ne correspondent pas';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _resetPassword,
                child: _isLoading
                    ? SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : Text('Réinitialiser le mot de passe'),
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 4. Gérer le Deep Linking (Optionnel)

```dart
import 'package:uni_links/uni_links.dart';
import 'dart:async';

class DeepLinkHandler {
  StreamSubscription? _sub;

  void initUniLinks(BuildContext context) async {
    // Gérer les liens lorsque l'app est déjà ouverte
    _sub = linkStream.listen((String? link) {
      if (link != null) {
        _handleDeepLink(context, link);
      }
    });

    // Gérer le lien initial (quand l'app est fermée)
    try {
      final initialLink = await getInitialLink();
      if (initialLink != null) {
        _handleDeepLink(context, initialLink);
      }
    } catch (e) {
      print('Erreur deep link: $e');
    }
  }

  void _handleDeepLink(BuildContext context, String link) {
    final uri = Uri.parse(link);

    if (uri.host == 'reset-password') {
      final token = uri.queryParameters['token'];
      final email = uri.queryParameters['email'];

      Navigator.pushNamed(
        context,
        '/reset-password',
        arguments: {
          'token': token,
          'email': email,
        },
      );
    }
  }

  void dispose() {
    _sub?.cancel();
  }
}
```

## Notes importantes

1. **Sécurité** :

    - Les tokens expirent après 60 minutes (configurable dans `config/auth.php`)
    - Un token ne peut être utilisé qu'une seule fois
    - L'email doit correspondre au token

2. **Email** :

    - Assurez-vous que votre configuration email est correcte
    - Testez avec Mailtrap en développement
    - Le token est visible dans l'email pour faciliter le copier-coller

3. **Deep Linking** :

    - Configurez `FRONTEND_URL` dans `.env` avec le scheme de votre app
    - Exemple : `myapp://reset-password` ou `https://votredomaine.com/reset-password`

4. **Tests** :
    - Testez d'abord avec Mailtrap
    - Vérifiez que les emails sont bien reçus
    - Testez le deep linking sur un appareil réel

## Dépannage

### L'email n'est pas envoyé

-   Vérifiez les logs : `storage/logs/laravel.log`
-   Vérifiez la configuration email dans `.env`
-   Testez la connexion SMTP

### Token invalide

-   Le token peut avoir expiré (60 minutes par défaut)
-   Le token a peut-être déjà été utilisé
-   Vérifiez que l'email correspond

### Deep linking ne fonctionne pas

-   Vérifiez la configuration dans AndroidManifest.xml et Info.plist
-   Testez avec `adb` sur Android : `adb shell am start -a android.intent.action.VIEW -d "myapp://reset-password?token=xxx&email=xxx"`
