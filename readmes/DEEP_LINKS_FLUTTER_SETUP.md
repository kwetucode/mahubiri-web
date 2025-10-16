# Configuration des Deep Links pour Flutter

# ==========================================

## Backend Laravel - Ajoutez ces variables à votre fichier .env

# Schéma de deep link de votre application Flutter

# Exemple: si vous utilisez 'mahubiri', vos deep links seront: mahubiri://verification-success

FLUTTER_APP_SCHEME=mahubiri

# URLs des stores (optionnel - utilisé comme fallback)

PLAY_STORE_URL=https://play.google.com/store/apps/details?id=com.votre.app
APP_STORE_URL=https://apps.apple.com/app/votre-app/id123456789

# URL de votre backend (important pour générer les bons liens)

APP_URL=https://votre-domaine.com

## Frontend Flutter - Configuration des Deep Links

### 1. Configuration Android (android/app/src/main/AndroidManifest.xml)

Ajoutez ceci dans votre activité principale (<activity> tag):

```xml
<activity
    android:name=".MainActivity"
    ...>

    <!-- Deep Links Configuration -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />

        <!-- Remplacez 'mahubiri' par votre schéma -->
        <data android:scheme="mahubiri" />
    </intent-filter>

    <!-- Universal Links Configuration (optionnel mais recommandé) -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />

        <!-- Remplacez par votre domaine -->
        <data
            android:scheme="https"
            android:host="votre-domaine.com"
            android:pathPrefix="/email" />
        <data
            android:scheme="https"
            android:host="votre-domaine.com"
            android:pathPrefix="/reset-password" />
    </intent-filter>
</activity>
```

### 2. Configuration iOS (ios/Runner/Info.plist)

Ajoutez ceci dans votre Info.plist:

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLName</key>
        <string>com.votre.app</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <!-- Remplacez 'mahubiri' par votre schéma -->
            <string>mahubiri</string>
        </array>
    </dict>
</array>

<!-- Universal Links (optionnel mais recommandé) -->
<key>com.apple.developer.associated-domains</key>
<array>
    <string>applinks:votre-domaine.com</string>
</array>
```

### 3. Packages Flutter requis

Ajoutez dans votre pubspec.yaml:

```yaml
dependencies:
    uni_links: ^0.5.1 # ou app_links: ^3.4.5 (plus récent)
    # OU
    go_router: ^13.0.0 # Si vous utilisez go_router (recommandé)
```

### 4. Exemple de code Flutter pour gérer les deep links

#### Option A: Avec uni_links (simple)

```dart
import 'package:uni_links/uni_links.dart';
import 'dart:async';

class DeepLinkService {
  StreamSubscription? _sub;

  void initDeepLinks(BuildContext context) {
    // Gérer le lien initial (app fermée)
    _handleInitialLink(context);

    // Gérer les liens entrants (app ouverte/en arrière-plan)
    _handleIncomingLinks(context);
  }

  Future<void> _handleInitialLink(BuildContext context) async {
    try {
      final initialLink = await getInitialLink();
      if (initialLink != null) {
        _processDeepLink(context, initialLink);
      }
    } catch (e) {
      print('Erreur lors de la récupération du lien initial: $e');
    }
  }

  void _handleIncomingLinks(BuildContext context) {
    _sub = linkStream.listen(
      (String? link) {
        if (link != null) {
          _processDeepLink(context, link);
        }
      },
      onError: (err) {
        print('Erreur de deep link: $err');
      },
    );
  }

  void _processDeepLink(BuildContext context, String link) {
    final uri = Uri.parse(link);

    print('Deep link reçu: $link');
    print('Host: ${uri.host}');
    print('Paramètres: ${uri.queryParameters}');

    switch (uri.host) {
      case 'verification-success':
        _handleEmailVerification(context, uri);
        break;
      case 'verification-failed':
        _handleVerificationError(context, uri);
        break;
      case 'reset-password':
        _handlePasswordReset(context, uri);
        break;
      case 'reset-password-failed':
        _handleResetError(context, uri);
        break;
      default:
        print('Route de deep link non reconnue: ${uri.host}');
    }
  }

  void _handleEmailVerification(BuildContext context, Uri uri) {
    final status = uri.queryParameters['status'];
    final message = uri.queryParameters['message'] ?? 'Email vérifié';

    if (status == 'verified' || status == 'already_verified') {
      // Afficher un message de succès
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: Colors.green,
        ),
      );

      // Rediriger vers la page d'accueil ou actualiser le profil
      Navigator.of(context).pushReplacementNamed('/home');

      // Actualiser le statut de l'utilisateur
      // Provider.of<AuthProvider>(context, listen: false).refreshUser();
    }
  }

  void _handleVerificationError(BuildContext context, Uri uri) {
    final message = uri.queryParameters['message'] ?? 'Erreur de vérification';

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  void _handlePasswordReset(BuildContext context, Uri uri) {
    final token = uri.queryParameters['token'];
    final email = uri.queryParameters['email'];

    if (token != null && email != null) {
      // Naviguer vers la page de réinitialisation de mot de passe
      Navigator.of(context).pushReplacementNamed(
        '/reset-password',
        arguments: {
          'token': token,
          'email': email,
        },
      );
    } else {
      _handleResetError(context, uri);
    }
  }

  void _handleResetError(BuildContext context, Uri uri) {
    final message = uri.queryParameters['message'] ??
                    'Erreur de réinitialisation du mot de passe';

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  void dispose() {
    _sub?.cancel();
  }
}
```

#### Option B: Avec GoRouter (recommandé pour navigation complexe)

```dart
import 'package:go_router/go_router.dart';

final router = GoRouter(
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => HomePage(),
    ),
    GoRoute(
      path: '/reset-password',
      builder: (context, state) {
        final token = state.queryParams['token'];
        final email = state.queryParams['email'];
        return ResetPasswordPage(token: token, email: email);
      },
    ),
  ],
  // Gérer les deep links
  redirect: (context, state) {
    // Logique de redirection si nécessaire
    return null;
  },
);
```

### 5. Utilisation dans votre app principale

```dart
void main() {
  runApp(MyApp());
}

class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final DeepLinkService _deepLinkService = DeepLinkService();

  @override
  void initState() {
    super.initState();
    // Initialiser les deep links après le premier frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _deepLinkService.initDeepLinks(context);
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
      home: HomePage(),
      // Définir vos routes
      routes: {
        '/home': (context) => HomePage(),
        '/reset-password': (context) => ResetPasswordPage(),
      },
    );
  }
}
```

### 6. Test des Deep Links

#### Sur Android:

```bash
# Via ADB
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"

# Ou depuis le terminal
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://reset-password?token=test123&email=test@example.com"
```

#### Sur iOS:

```bash
# Via xcrun (simulateur)
xcrun simctl openurl booted "mahubiri://verification-success?status=verified&message=Test"
```

#### Via navigateur web (si universal links configurés):

```
https://votre-domaine.com/email/verify/1/hash123
https://votre-domaine.com/reset-password?token=abc123&email=test@example.com
```

### 7. Vérification du bon fonctionnement

1. **Backend**: Les emails doivent contenir des liens comme:

    - `https://votre-domaine.com/email/verify/{id}/{hash}`
    - `https://votre-domaine.com/reset-password?token=xxx&email=xxx`

2. **Clic sur le lien**: La page web intermédiaire s'ouvre et tente d'ouvrir l'app

3. **Application Flutter**: L'app s'ouvre automatiquement et traite le deep link

4. **Navigation**: L'utilisateur est redirigé vers la bonne page dans l'app

### 8. Débogage

Ajoutez des logs pour suivre le flux:

```dart
void _processDeepLink(BuildContext context, String link) {
  print('=== DEEP LINK REÇU ===');
  print('URL complète: $link');

  final uri = Uri.parse(link);
  print('Schéma: ${uri.scheme}');
  print('Host: ${uri.host}');
  print('Path: ${uri.path}');
  print('Paramètres: ${uri.queryParameters}');
  print('=====================');

  // ... reste du code
}
```

### 9. Problèmes courants et solutions

**Problème**: Le deep link ne s'ouvre pas

-   Vérifiez que le schéma dans AndroidManifest.xml/Info.plist correspond à FLUTTER_APP_SCHEME
-   Vérifiez que l'app est installée sur le device
-   Sur Android, vérifiez avec `adb logcat` si des erreurs apparaissent

**Problème**: L'app s'ouvre mais rien ne se passe

-   Vérifiez que `initDeepLinks()` est bien appelé
-   Ajoutez des logs pour voir si le lien est reçu
-   Vérifiez que le context est disponible quand vous traitez le lien

**Problème**: Sur iOS, le lien ne fonctionne qu'en debug

-   Assurez-vous que les Associated Domains sont configurés dans Xcode
-   Vérifiez que le fichier `.well-known/apple-app-site-association` existe sur votre serveur

### 10. Fichier apple-app-site-association (pour Universal Links iOS)

Créez ce fichier à la racine de votre domaine: `https://votre-domaine.com/.well-known/apple-app-site-association`

```json
{
    "applinks": {
        "apps": [],
        "details": [
            {
                "appID": "TEAM_ID.com.votre.app",
                "paths": ["/email/*", "/reset-password"]
            }
        ]
    }
}
```

Remplacez:

-   `TEAM_ID`: Votre Apple Team ID (trouvable dans Apple Developer Account)
-   `com.votre.app`: Votre bundle identifier

Le fichier doit être servi avec le header: `Content-Type: application/json`
