# 📦 Fichiers Prêts à l'Emploi - Flutter

Ce dossier contient tous les fichiers Flutter dont vous avez besoin pour implémenter les deep links dans votre application.

## 📁 Structure des Fichiers

```
examples/
├── DeepLinkServiceFlutter.dart         # Version simple (avec BuildContext)
├── DeepLinkServiceFlutter_v2.dart      # Version améliorée (avec GlobalKey) ⭐ RECOMMANDÉ
├── MainFlutterComplete.dart            # Exemple complet de main.dart
└── ResetPasswordPageExample.dart       # Exemple de page de reset password
```

## 🚀 Installation Rapide (5 minutes)

### Étape 1: Copier le Service Deep Links

**Fichier à copier**: `DeepLinkServiceFlutter_v2.dart`

Copiez ce fichier dans votre projet Flutter:

```
votre_projet_flutter/
└── lib/
    └── services/
        └── deep_link_service.dart  ← Copiez ici
```

### Étape 2: Modifier votre main.dart

Copiez le code de `MainFlutterComplete.dart` ou adaptez votre main.dart existant:

```dart
import 'package:flutter/material.dart';
import 'services/deep_link_service.dart';

void main() => runApp(MyApp());

class MyApp extends StatefulWidget {
  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  late final DeepLinkService _deepLinkService;

  @override
  void initState() {
    super.initState();
    _deepLinkService = DeepLinkService(navigatorKey);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _deepLinkService.init();
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
      navigatorKey: navigatorKey, // IMPORTANT!
      routes: {
        '/': (context) => HomePage(),
        '/home': (context) => HomePage(),
        '/reset-password': (context) => ResetPasswordPage(),
      },
    );
  }
}
```

### Étape 3: Ajouter le Package

Dans `pubspec.yaml`:

```yaml
dependencies:
    uni_links: ^0.5.1
```

Puis:

```bash
flutter pub get
```

### Étape 4: Configurer Android

Fichier: `android/app/src/main/AndroidManifest.xml`

```xml
<activity android:name=".MainActivity" ...>
    <!-- Vos intent-filters existants -->

    <!-- AJOUTEZ CECI -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />
        <data android:scheme="mahubiri" />
    </intent-filter>
</activity>
```

### Étape 5: Configurer iOS

Fichier: `ios/Runner/Info.plist`

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>mahubiri</string>
        </array>
    </dict>
</array>
```

### Étape 6: Test

```bash
# Désinstaller l'app (important après modif native)
adb uninstall com.votre.package

# Rebuilder
flutter run

# Tester
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"
```

## 📝 Description des Fichiers

### DeepLinkServiceFlutter.dart

**Quand l'utiliser**: Version simple si vous maîtrisez Flutter

**Caractéristiques**:

-   Utilise BuildContext directement
-   Plus simple à comprendre
-   Nécessite que le context soit disponible

**Limitations**:

-   Peut avoir des problèmes de timing
-   Context parfois non disponible

### DeepLinkServiceFlutter_v2.dart ⭐

**Quand l'utiliser**: **RECOMMANDÉ** - Version robuste

**Caractéristiques**:

-   Utilise GlobalKey<NavigatorState>
-   Gestion d'erreur robuste
-   Logs de debug détaillés
-   Méthode `checkStatus()` pour diagnostic
-   Gère les cas où le context n'est pas disponible

**Avantages**:

-   ✅ Fonctionne à coup sûr
-   ✅ Meilleure gestion du lifecycle
-   ✅ Facilite le débogage

### MainFlutterComplete.dart

**Contenu**:

-   Exemple complet de main.dart
-   Avec GlobalKey
-   Avec NavigatorObserver pour debug
-   Toutes les bonnes pratiques

**Usage**: Copier/adapter selon vos besoins

### ResetPasswordPageExample.dart

**Contenu**:

-   Page complète de réinitialisation de mot de passe
-   Récupération des arguments du deep link
-   Appel API pour reset
-   Validation du formulaire

**Usage**: Adapter à votre API et design

## 🎯 Quelle Version Choisir ?

### Débutant Flutter

→ Utilisez `DeepLinkServiceFlutter_v2.dart` + `MainFlutterComplete.dart`
→ Copiez exactement le code

### Intermédiaire Flutter

→ Utilisez `DeepLinkServiceFlutter_v2.dart`
→ Adaptez selon votre architecture (Provider, Bloc, GetX, etc.)

### Expert Flutter

→ Utilisez `DeepLinkServiceFlutter.dart` comme base
→ Personnalisez selon vos besoins

## 🔧 Personnalisation

### Changer le Schéma

Si vous ne voulez pas utiliser "mahubiri":

1. **Backend** (`.env`):

    ```bash
    FLUTTER_APP_SCHEME=monapp
    ```

2. **Flutter** (AndroidManifest.xml):

    ```xml
    <data android:scheme="monapp" />
    ```

3. **Flutter** (Info.plist):

    ```xml
    <string>monapp</string>
    ```

4. **Flutter** (deep_link_service.dart):
    ```dart
    if (uri.scheme != 'monapp') { // Changez ici
      return;
    }
    ```

### Ajouter des Routes Personnalisées

Dans `deep_link_service.dart`, ajoutez des cas:

```dart
switch (uri.host) {
  case 'verification-success':
    _handleEmailVerificationSuccess(context, uri);
    break;

  // AJOUTEZ VOS ROUTES ICI
  case 'mon-custom-route':
    _handleCustomRoute(context, uri);
    break;

  default:
    debugPrint('⚠️ Route non reconnue: ${uri.host}');
}
```

### Intégration avec Provider

```dart
void _handleEmailVerificationSuccess(BuildContext context, Uri uri) {
  // ...

  // Actualiser l'état avec Provider
  try {
    Provider.of<AuthProvider>(context, listen: false).refreshUser();
  } catch (e) {
    debugPrint('Erreur refresh: $e');
  }

  // ...
}
```

### Intégration avec Bloc

```dart
void _handleEmailVerificationSuccess(BuildContext context, Uri uri) {
  // ...

  // Actualiser l'état avec Bloc
  context.read<AuthBloc>().add(RefreshUserEvent());

  // ...
}
```

### Intégration avec GetX

```dart
void _handleEmailVerificationSuccess(BuildContext context, Uri uri) {
  // ...

  // Actualiser l'état avec GetX
  Get.find<AuthController>().refreshUser();

  // ...
}
```

## 🧪 Tests

### Test Minimal

Créez un fichier de test simple:

```dart
// test_deep_links.dart
import 'package:flutter/material.dart';
import 'package:uni_links/uni_links.dart';
import 'dart:async';

void main() => runApp(TestApp());

class TestApp extends StatefulWidget {
  @override
  State<TestApp> createState() => _TestAppState();
}

class _TestAppState extends State<TestApp> {
  final GlobalKey<NavigatorState> _key = GlobalKey<NavigatorState>();
  StreamSubscription? _sub;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _sub = linkStream.listen((String? link) {
        print('📱 DEEP LINK REÇU: $link');
        ScaffoldMessenger.of(_key.currentContext!).showSnackBar(
          SnackBar(content: Text('Deep link: $link')),
        );
      });
    });
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: _key,
      home: Scaffold(
        appBar: AppBar(title: Text('Test Deep Links')),
        body: Center(child: Text('Cliquez sur un lien d\'email')),
      ),
    );
  }
}
```

Test:

```bash
flutter run
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://test"
```

## 📚 Documentation Complète

-   **Guide rapide**: `../readmes/QUICK_START_DEEP_LINKS.md`
-   **Troubleshooting**: `../readmes/TROUBLESHOOTING_DEEP_LINKS.md`
-   **Solutions courantes**: `../readmes/COMMON_ISSUES_SOLUTIONS.md`
-   **Configuration complète**: `../readmes/DEEP_LINKS_FLUTTER_SETUP.md`

## 🆘 Support

Si vous rencontrez des problèmes:

1. **Diagnostic automatique**:

    ```powershell
    ..\diagnose-deep-links.ps1
    ```

2. **Consultez les solutions courantes**:

    ```bash
    code ../readmes/COMMON_ISSUES_SOLUTIONS.md
    ```

3. **Vérifiez vos logs**:
    ```bash
    flutter run --verbose
    flutter logs
    ```

## ✅ Validation

Votre implémentation est correcte si:

-   [ ] Test ADB ouvre l'app
-   [ ] Les logs Flutter montrent "Deep link reçu:"
-   [ ] Clic sur email ouvre l'app
-   [ ] SnackBar s'affiche correctement
-   [ ] Navigation fonctionne

## 🎉 Félicitations !

Si tout fonctionne, vous avez maintenant:

-   ✅ Deep links fonctionnels
-   ✅ Navigation automatique vers l'app
-   ✅ Vérification d'email fluide
-   ✅ Reset password sans copier/coller

---

**Version**: 2.0  
**Date**: Octobre 2025  
**Status**: Production Ready ✅
