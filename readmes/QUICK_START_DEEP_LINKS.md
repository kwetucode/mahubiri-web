# Guide de Configuration Rapide - Deep Links Flutter/Laravel

## 🎯 Problème résolu

Auparavant, lorsqu'un utilisateur cliquait sur un lien de vérification d'email ou de réinitialisation de mot de passe dans son email, il voyait une page JSON au lieu d'être redirigé vers l'application Flutter.

Maintenant, le lien ouvre automatiquement l'application Flutter avec les bonnes informations.

## 🚀 Configuration Backend (Laravel) - 5 minutes

### 1. Ajouter les variables d'environnement

Ajoutez ces lignes dans votre fichier `.env`:

```bash
# Remplacez 'mahubiri' par le nom de votre app (en minuscules, sans espaces)
FLUTTER_APP_SCHEME=mahubiri

# URLs optionnelles des stores (pour fallback)
PLAY_STORE_URL=https://play.google.com/store/apps/details?id=com.votre.app
APP_STORE_URL=https://apps.apple.com/app/votre-app/id123456789
```

### 2. Tester le backend

Redémarrez votre serveur Laravel:

```bash
php artisan config:clear
php artisan cache:clear
php artisan serve
```

Les fichiers suivants ont été créés/modifiés:

-   ✅ `app/Http/Controllers/Web/EmailVerificationWebController.php` (nouveau)
-   ✅ `app/Http/Controllers/Web/PasswordResetWebController.php` (nouveau)
-   ✅ `resources/views/redirect-to-app.blade.php` (nouveau)
-   ✅ `routes/web.php` (modifié)
-   ✅ `app/Notifications/CustomVerifyEmail.php` (modifié)
-   ✅ `app/Notifications/CustomResetPasswordNotification.php` (modifié)
-   ✅ `config/app.php` (modifié)

### 3. Tester l'envoi d'email

Envoyez un email de test:

```bash
# Email de vérification
curl -X POST http://localhost:8000/api/auth/email/resend \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Email de réinitialisation de mot de passe
curl -X POST http://localhost:8000/api/auth/password/email \
  -H "Content-Type: application/json" \
  -d '{"email":"votre@email.com"}'
```

Le lien dans l'email devrait ressembler à:

-   `http://localhost:8000/email/verify/1/abc123...`
-   `http://localhost:8000/reset-password?token=xyz&email=user@example.com`

## 📱 Configuration Frontend (Flutter) - 15 minutes

### 1. Ajouter le package

Dans `pubspec.yaml`:

```yaml
dependencies:
    uni_links: ^0.5.1
```

Puis:

```bash
flutter pub get
```

### 2. Configuration Android

**Fichier**: `android/app/src/main/AndroidManifest.xml`

Ajoutez dans la balise `<activity>` de `MainActivity`:

```xml
<activity android:name=".MainActivity" ...>
    <!-- Vos intent-filters existants -->

    <!-- AJOUTEZ CECI -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />

        <!-- Utilisez le même schéma que dans .env -->
        <data android:scheme="mahubiri" />
    </intent-filter>
</activity>
```

### 3. Configuration iOS

**Fichier**: `ios/Runner/Info.plist`

Ajoutez ceci (avant `</dict></plist>`):

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>mahubiri</string>
        </array>
    </dict>
</array>
```

### 4. Créer le service de Deep Links

**Fichier**: `lib/services/deep_link_service.dart`

```dart
import 'package:flutter/material.dart';
import 'package:uni_links/uni_links.dart';
import 'dart:async';

class DeepLinkService {
  StreamSubscription? _sub;

  // Initialiser les deep links
  void init(BuildContext context) {
    _handleInitialLink(context);
    _handleIncomingLinks(context);
  }

  // Gérer le lien initial (app fermée)
  Future<void> _handleInitialLink(BuildContext context) async {
    try {
      final initialLink = await getInitialLink();
      if (initialLink != null) {
        _processLink(context, initialLink);
      }
    } catch (e) {
      print('Erreur lien initial: $e');
    }
  }

  // Gérer les liens entrants (app ouverte)
  void _handleIncomingLinks(BuildContext context) {
    _sub = linkStream.listen(
      (String? link) {
        if (link != null) _processLink(context, link);
      },
      onError: (err) => print('Erreur deep link: $err'),
    );
  }

  // Traiter le deep link
  void _processLink(BuildContext context, String link) {
    final uri = Uri.parse(link);
    print('Deep link: ${uri.host}');

    switch (uri.host) {
      case 'verification-success':
        _showSuccess(context, uri.queryParameters['message'] ?? 'Email vérifié!');
        Navigator.of(context).pushReplacementNamed('/home');
        break;

      case 'verification-failed':
        _showError(context, uri.queryParameters['message'] ?? 'Erreur de vérification');
        break;

      case 'reset-password':
        final token = uri.queryParameters['token'];
        final email = uri.queryParameters['email'];
        if (token != null && email != null) {
          Navigator.of(context).pushReplacementNamed(
            '/reset-password',
            arguments: {'token': token, 'email': email},
          );
        }
        break;

      case 'reset-password-failed':
        _showError(context, uri.queryParameters['message'] ?? 'Erreur de réinitialisation');
        break;
    }
  }

  void _showSuccess(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
    );
  }

  void _showError(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void dispose() => _sub?.cancel();
}
```

### 5. Intégrer dans votre app

**Fichier**: `lib/main.dart`

```dart
import 'package:flutter/material.dart';
import 'services/deep_link_service.dart';

void main() => runApp(MyApp());

class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final _deepLinkService = DeepLinkService();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _deepLinkService.init(context);
    });
  }

  @override
  void dispose() {
    _deepLinkService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mahubiri',
      initialRoute: '/',
      routes: {
        '/': (context) => HomePage(),
        '/home': (context) => HomePage(),
        '/reset-password': (context) => ResetPasswordPage(),
      },
    );
  }
}
```

### 6. Créer la page de réinitialisation

**Fichier**: `lib/pages/reset_password_page.dart`

```dart
import 'package:flutter/material.dart';

class ResetPasswordPage extends StatefulWidget {
  @override
  _ResetPasswordPageState createState() => _ResetPasswordPageState();
}

class _ResetPasswordPageState extends State<ResetPasswordPage> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    // Récupérer les arguments passés via le deep link
    final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
    final token = args?['token'] ?? '';
    final email = args?['email'] ?? '';

    return Scaffold(
      appBar: AppBar(title: Text('Nouveau mot de passe')),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              Text('Email: $email', style: TextStyle(fontSize: 16)),
              SizedBox(height: 20),

              TextFormField(
                controller: _passwordController,
                decoration: InputDecoration(labelText: 'Nouveau mot de passe'),
                obscureText: true,
                validator: (v) => v!.length < 8 ? 'Min 8 caractères' : null,
              ),

              TextFormField(
                controller: _confirmPasswordController,
                decoration: InputDecoration(labelText: 'Confirmer mot de passe'),
                obscureText: true,
                validator: (v) => v != _passwordController.text
                    ? 'Les mots de passe ne correspondent pas'
                    : null,
              ),

              SizedBox(height: 20),

              ElevatedButton(
                onPressed: _isLoading ? null : () => _resetPassword(token, email),
                child: _isLoading
                    ? CircularProgressIndicator()
                    : Text('Réinitialiser'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _resetPassword(String token, String email) async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      // Appel à votre API
      final response = await http.post(
        Uri.parse('YOUR_API_URL/api/auth/password/reset'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'token': token,
          'email': email,
          'password': _passwordController.text,
          'password_confirmation': _confirmPasswordController.text,
        }),
      );

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Mot de passe réinitialisé avec succès!')),
        );
        Navigator.of(context).pushReplacementNamed('/login');
      } else {
        throw Exception('Erreur lors de la réinitialisation');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }
}
```

## 🧪 Test complet

### Test sur Android (via ADB):

```bash
# Test vérification email
adb shell am start -W -a android.intent.action.VIEW \
  -d "mahubiri://verification-success?status=verified&message=Test"

# Test reset password
adb shell am start -W -a android.intent.action.VIEW \
  -d "mahubiri://reset-password?token=abc123&email=test@example.com"
```

### Test sur iOS (simulateur):

```bash
# Test vérification email
xcrun simctl openurl booted \
  "mahubiri://verification-success?status=verified&message=Test"

# Test reset password
xcrun simctl openurl booted \
  "mahubiri://reset-password?token=abc123&email=test@example.com"
```

### Test complet (email réel):

1. Inscrivez un utilisateur via votre API
2. Demandez l'envoi d'un email de vérification
3. Ouvrez l'email sur votre téléphone
4. Cliquez sur le lien "Vérifier mon email"
5. L'app devrait s'ouvrir automatiquement ✅

## 🐛 Dépannage

### L'app ne s'ouvre pas

1. **Vérifiez le schéma**: Le schéma dans `.env` doit correspondre à celui dans `AndroidManifest.xml` et `Info.plist`
2. **Réinstallez l'app**: Désinstallez et réinstallez après avoir modifié les fichiers natifs
3. **Vérifiez les logs**:

    ```bash
    # Android
    adb logcat | grep -i "mahubiri"

    # iOS
    xcrun simctl spawn booted log stream --predicate 'processImagePath contains "Runner"'
    ```

### L'app s'ouvre mais rien ne se passe

1. Vérifiez que `DeepLinkService.init()` est appelé
2. Ajoutez des `print()` dans `_processLink()` pour voir le lien reçu
3. Vérifiez que vos routes sont bien définies dans `MaterialApp`

### L'email n'est pas envoyé

1. Vérifiez la configuration mail dans `.env`
2. Consultez les logs Laravel: `tail -f storage/logs/laravel.log`
3. Testez en local avec Mailtrap ou MailHog

## 📚 Documentation complète

Pour plus de détails, consultez:

-   `readmes/DEEP_LINKS_FLUTTER_SETUP.md` - Guide complet avec tous les détails
-   [Documentation uni_links](https://pub.dev/packages/uni_links)
-   [Flutter Deep Linking](https://docs.flutter.dev/development/ui/navigation/deep-linking)

## ✅ Checklist de vérification

Backend Laravel:

-   [ ] Variables `.env` configurées (FLUTTER_APP_SCHEME)
-   [ ] Cache Laravel vidé (`php artisan config:clear`)
-   [ ] Emails de test envoyés avec succès
-   [ ] Les liens pointent vers les routes web (pas API)

Frontend Flutter:

-   [ ] Package `uni_links` installé
-   [ ] `AndroidManifest.xml` modifié
-   [ ] `Info.plist` modifié
-   [ ] `DeepLinkService` créé et initialisé
-   [ ] Routes configurées dans `MaterialApp`
-   [ ] Page de réinitialisation créée

Tests:

-   [ ] Test ADB/xcrun fonctionne
-   [ ] Clic sur lien email ouvre l'app
-   [ ] Vérification d'email fonctionne
-   [ ] Réinitialisation de mot de passe fonctionne

## 🎉 Félicitations !

Votre système de vérification d'email et de réinitialisation de mot de passe avec deep links est maintenant opérationnel !

Les utilisateurs peuvent maintenant:

1. Cliquer sur le lien dans leur email
2. Être automatiquement redirigés vers votre application Flutter
3. Effectuer l'action demandée (vérification ou réinitialisation)

Tout cela sans voir de page JSON ! 🚀
