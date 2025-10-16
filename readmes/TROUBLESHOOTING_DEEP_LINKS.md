# 🔧 Troubleshooting: Deep Links Non Écoutés

## 🎯 Problème

**Symptôme**: Le mail arrive, le lien se charge dans le navigateur et affiche la page HTML intermédiaire, mais l'application Flutter ne s'ouvre pas ou ne réagit pas au deep link.

## ✅ Checklist de Diagnostic

### 1. Vérification de la Configuration Android

#### AndroidManifest.xml

Fichier: `android/app/src/main/AndroidManifest.xml`

```xml
<activity
    android:name=".MainActivity"
    android:launchMode="singleTop"
    ...>

    <!-- Intent filter EXISTANT (ne pas supprimer) -->
    <intent-filter>
        <action android:name="android.intent.action.MAIN"/>
        <category android:name="android.intent.category.LAUNCHER"/>
    </intent-filter>

    <!-- AJOUTEZ CECI -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />
        <!-- Utilisez le même schéma que dans votre .env backend -->
        <data android:scheme="mahubiri" />
    </intent-filter>
</activity>
```

**Points importants:**

-   ✅ L'intent-filter doit être DANS la balise `<activity>` de `MainActivity`
-   ✅ Ne supprimez PAS l'intent-filter LAUNCHER existant
-   ✅ `android:scheme="mahubiri"` doit correspondre à `FLUTTER_APP_SCHEME` dans `.env`
-   ✅ `android:launchMode="singleTop"` est recommandé

### 2. Vérification de la Configuration iOS

#### Info.plist

Fichier: `ios/Runner/Info.plist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <!-- Votre configuration existante -->

    <!-- AJOUTEZ CECI AVANT </dict> -->
    <key>CFBundleURLTypes</key>
    <array>
        <dict>
            <key>CFBundleTypeRole</key>
            <string>Editor</string>
            <key>CFBundleURLName</key>
            <string>com.votre.app</string>
            <key>CFBundleURLSchemes</key>
            <array>
                <string>mahubiri</string>
            </array>
        </dict>
    </array>

    <!-- Pour iOS 9+ (si vous voulez aussi des universal links) -->
    <key>LSApplicationQueriesSchemes</key>
    <array>
        <string>mahubiri</string>
    </array>
</dict>
</plist>
```

### 3. Vérification du Code Flutter

#### Problème Commun #1: Contexte non disponible

❌ **Mauvais code:**

```dart
class _MyAppState extends State<MyApp> {
  final DeepLinkService _deepLinkService = DeepLinkService();

  @override
  void initState() {
    super.initState();
    // PROBLÈME: context n'est pas encore disponible ici!
    _deepLinkService.init(context);
  }
}
```

✅ **Bon code:**

```dart
class _MyAppState extends State<MyApp> {
  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  late final DeepLinkService _deepLinkService;

  @override
  void initState() {
    super.initState();
    _deepLinkService = DeepLinkService(navigatorKey);

    // Attendre que le widget tree soit construit
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _deepLinkService.init();
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey, // IMPORTANT!
      // ...
    );
  }
}
```

#### Problème Commun #2: Package non installé

Vérifiez votre `pubspec.yaml`:

```yaml
dependencies:
    flutter:
        sdk: flutter
    uni_links: ^0.5.1 # OU app_links: ^3.4.5 (plus récent)
```

Puis:

```bash
flutter pub get
```

#### Problème Commun #3: Routes non définies

```dart
MaterialApp(
  navigatorKey: navigatorKey,
  initialRoute: '/',
  routes: {
    '/': (context) => HomePage(),
    '/home': (context) => HomePage(),
    '/login': (context) => LoginPage(),
    '/profile': (context) => ProfilePage(),
    '/reset-password': (context) => ResetPasswordPage(),
  },
)
```

### 4. Test Direct de l'App (Sans Backend)

#### Test Android (ADB):

```bash
# 1. Vérifier que l'app est installée
adb shell pm list packages | findstr votre.package

# 2. Tester le deep link directement
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"

# 3. Voir les logs en temps réel
adb logcat | findstr -i "mahubiri"
```

#### Test iOS (Simulateur):

```bash
# Tester le deep link
xcrun simctl openurl booted "mahubiri://verification-success?status=verified&message=Test"

# Voir les logs
xcrun simctl spawn booted log stream --predicate 'processImagePath contains "Runner"'
```

**Si l'app ne s'ouvre pas avec ce test:**
→ Le problème est dans la configuration native (AndroidManifest.xml / Info.plist)

**Si l'app s'ouvre mais rien ne se passe:**
→ Le problème est dans le code Flutter (service non initialisé ou contexte manquant)

### 5. Vérification des Logs Flutter

Ajoutez ces logs de débogage dans votre service:

```dart
void init() {
  debugPrint('🚀 [DEEP LINK] Initialisation du service');
  debugPrint('🚀 [DEEP LINK] Navigator key: ${navigatorKey.currentState != null}');

  _handleInitialLink();
  _handleIncomingLinks();

  debugPrint('✅ [DEEP LINK] Service initialisé');
}

void _handleIncomingLinks() {
  debugPrint('👂 [DEEP LINK] Écoute des liens entrants...');

  _linkSubscription = linkStream.listen(
    (String? link) {
      debugPrint('📨 [DEEP LINK] Lien reçu: $link');
      if (link != null) {
        _processDeepLink(link);
      } else {
        debugPrint('⚠️ [DEEP LINK] Lien null reçu');
      }
    },
    onError: (err) {
      debugPrint('❌ [DEEP LINK] Erreur: $err');
    },
  );
}
```

Puis exécutez:

```bash
flutter run --verbose
```

### 6. Solution Complète Main.dart

Voici un exemple complet qui fonctionne à coup sûr:

```dart
import 'package:flutter/material.dart';
import 'services/deep_link_service.dart';

void main() {
  // Activer les logs en mode debug
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> with WidgetsBindingObserver {
  // Clé globale pour le navigator
  final GlobalKey<NavigatorState> _navigatorKey = GlobalKey<NavigatorState>();

  // Service de deep links
  late final DeepLinkService _deepLinkService;

  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();

    // Observer le cycle de vie de l'app
    WidgetsBinding.instance.addObserver(this);

    // Créer le service
    _deepLinkService = DeepLinkService(_navigatorKey);

    // Initialiser après le premier frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_isInitialized) {
        debugPrint('🚀 [APP] Initialisation des deep links');
        _deepLinkService.init();
        _isInitialized = true;

        // Vérifier le statut après 2 secondes
        Future.delayed(const Duration(seconds: 2), () {
          _deepLinkService.checkStatus();
        });
      }
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    debugPrint('🔄 [APP] Lifecycle: $state');

    // Réinitialiser si l'app revient au premier plan
    if (state == AppLifecycleState.resumed && !_isInitialized) {
      debugPrint('🔄 [APP] App resumed, réinitialisation des deep links');
      _deepLinkService.init();
      _isInitialized = true;
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _deepLinkService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mahubiri',
      debugShowCheckedModeBanner: false,

      // IMPORTANT: Passer la clé
      navigatorKey: _navigatorKey,

      theme: ThemeData(
        primarySwatch: Colors.blue,
        useMaterial3: true,
      ),

      initialRoute: '/',

      // Définir TOUTES les routes
      routes: {
        '/': (context) => const HomePage(),
        '/home': (context) => const HomePage(),
        '/login': (context) => const LoginPage(),
        '/profile': (context) => const ProfilePage(),
        '/reset-password': (context) => const ResetPasswordPage(),
      },

      // Observer la navigation (optionnel, pour debug)
      navigatorObservers: [
        _LoggingNavigatorObserver(),
      ],
    );
  }
}

// NavigatorObserver pour déboguer
class _LoggingNavigatorObserver extends NavigatorObserver {
  @override
  void didPush(Route<dynamic> route, Route<dynamic>? previousRoute) {
    debugPrint('📍 [NAV] Push -> ${route.settings.name}');
  }

  @override
  void didPop(Route<dynamic> route, Route<dynamic>? previousRoute) {
    debugPrint('📍 [NAV] Pop <- ${route.settings.name}');
  }

  @override
  void didReplace({Route<dynamic>? newRoute, Route<dynamic>? oldRoute}) {
    debugPrint('📍 [NAV] Replace ${oldRoute?.settings.name} -> ${newRoute?.settings.name}');
  }
}

// Pages placeholder (remplacez par vos vraies pages)
class HomePage extends StatelessWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Home')),
      body: const Center(child: Text('Home Page')),
    );
  }
}

class LoginPage extends StatelessWidget {
  const LoginPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login')),
      body: const Center(child: Text('Login Page')),
    );
  }
}

class ProfilePage extends StatelessWidget {
  const ProfilePage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profile')),
      body: const Center(child: Text('Profile Page')),
    );
  }
}

class ResetPasswordPage extends StatelessWidget {
  const ResetPasswordPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    final email = args?['email'] ?? 'N/A';
    final token = args?['token'] ?? 'N/A';

    return Scaffold(
      appBar: AppBar(title: const Text('Reset Password')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('Email: $email'),
            Text('Token: ${token.substring(0, 10)}...'),
            const SizedBox(height: 20),
            const Text('Ici: formulaire de réinitialisation'),
          ],
        ),
      ),
    );
  }
}
```

### 7. Processus de Débogage Étape par Étape

1. **Désinstaller l'app complètement:**

    ```bash
    # Android
    adb uninstall com.votre.package

    # iOS
    xcrun simctl uninstall booted com.votre.package
    ```

2. **Nettoyer le projet:**

    ```bash
    flutter clean
    flutter pub get
    ```

3. **Rebuilder:**

    ```bash
    flutter run --verbose
    ```

4. **Tester avec ADB/xcrun:**

    ```bash
    adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"
    ```

5. **Observer les logs:**
    ```bash
    # Dans un autre terminal
    flutter logs
    ```

### 8. Problèmes Spécifiques et Solutions

#### L'app s'ouvre mais revient immédiatement au navigateur

**Cause**: Configuration `android:launchMode` incorrecte

**Solution**: Dans `AndroidManifest.xml`:

```xml
<activity
    android:name=".MainActivity"
    android:launchMode="singleTop"
    android:taskAffinity=""
    android:excludeFromRecents="false">
```

#### Les paramètres du deep link sont perdus

**Cause**: Encodage URL incorrect

**Solution**: Vérifiez que les paramètres sont encodés:

```dart
final encodedEmail = Uri.encodeComponent(email);
final deepLink = "mahubiri://reset-password?token=$token&email=$encodedEmail";
```

#### L'app s'ouvre une première fois puis plus jamais

**Cause**: Le service n'est pas réinitialisé

**Solution**: Ajoutez le `WidgetsBindingObserver`:

```dart
class _MyAppState extends State<MyApp> with WidgetsBindingObserver {
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _deepLinkService.init();
    }
  }
}
```

## 🎯 Test de Validation Final

### Étape 1: Test ADB

```bash
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=TestDirect"
```

**Attendu**: L'app s'ouvre et affiche un SnackBar vert

### Étape 2: Test Email Backend

1. Envoyez un email de vérification via votre API
2. Ouvrez l'email sur votre téléphone
3. Cliquez sur le lien
4. **Attendu**: Page HTML → App s'ouvre → SnackBar vert

### Étape 3: Test Reset Password

```bash
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://reset-password?token=test123&email=test@example.com"
```

**Attendu**: L'app s'ouvre sur la page de reset password

## 📞 Besoin d'Aide ?

Si aucune solution ne fonctionne, partagez:

1. Les logs de `flutter run --verbose`
2. Le contenu de votre `AndroidManifest.xml`
3. Votre code `main.dart`
4. Le résultat du test ADB

---

**Version**: 2.0  
**Date**: Octobre 2025  
**Status**: Guide complet de troubleshooting
