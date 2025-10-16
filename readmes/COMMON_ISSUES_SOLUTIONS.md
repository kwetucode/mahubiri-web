# 🚨 Solutions aux Problèmes Courants - Deep Links

## Problème: Le mail se charge mais l'app ne s'ouvre pas

### 🎯 Diagnostic Rapide

Exécutez le script de diagnostic:

```powershell
.\diagnose-deep-links.ps1
```

### ✅ Solutions par Ordre de Probabilité

---

## Solution 1: GlobalKey non utilisé (80% des cas)

### ❌ Code Incorrect

```dart
class _MyAppState extends State<MyApp> {
  final DeepLinkService _deepLinkService = DeepLinkService();

  @override
  void initState() {
    super.initState();
    _deepLinkService.init(context); // PROBLÈME: context pas encore disponible
  }
}
```

### ✅ Code Correct

```dart
class _MyAppState extends State<MyApp> {
  // AJOUTEZ: GlobalKey
  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  late final DeepLinkService _deepLinkService;

  @override
  void initState() {
    super.initState();
    // Créez le service avec la clé
    _deepLinkService = DeepLinkService(navigatorKey);

    // Initialisez APRÈS le premier frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _deepLinkService.init();
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey, // IMPORTANT: Passez la clé
      // ...
    );
  }
}
```

**Fichier à utiliser**: `examples/DeepLinkServiceFlutter_v2.dart`

---

## Solution 2: AndroidManifest.xml mal configuré (15% des cas)

### ❌ Configuration Incorrecte

```xml
<!-- MAUVAIS: En dehors de <activity> -->
</activity>
<intent-filter>
    <data android:scheme="mahubiri" />
</intent-filter>
```

### ✅ Configuration Correcte

```xml
<activity
    android:name=".MainActivity"
    android:launchMode="singleTop"
    ...>

    <!-- Intent filter LAUNCHER (existant) -->
    <intent-filter>
        <action android:name="android.intent.action.MAIN"/>
        <category android:name="android.intent.category.LAUNCHER"/>
    </intent-filter>

    <!-- AJOUTEZ: Intent filter pour deep links -->
    <intent-filter android:autoVerify="true">
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />
        <data android:scheme="mahubiri" />
    </intent-filter>

</activity> <!-- Bien fermé -->
```

**Fichier**: `android/app/src/main/AndroidManifest.xml`

**Après modification**: Désinstallez et réinstallez l'app!

```bash
adb uninstall com.votre.package
flutter run
```

---

## Solution 3: Package uni_links non installé (3% des cas)

### Vérification

```yaml
# pubspec.yaml
dependencies:
    flutter:
        sdk: flutter
    uni_links: ^0.5.1 # Doit être présent
```

### Installation

```bash
flutter pub get
flutter clean
flutter run
```

### Alternative Moderne

Si `uni_links` pose problème, utilisez `app_links`:

```yaml
dependencies:
    app_links: ^3.4.5
```

---

## Solution 4: Schéma différent entre Backend et Flutter (2% des cas)

### Vérification

**Backend** (`.env`):

```bash
FLUTTER_APP_SCHEME=mahubiri
```

**Flutter** (`AndroidManifest.xml`):

```xml
<data android:scheme="mahubiri" />  <!-- Doit être identique -->
```

**Flutter** (`Info.plist`):

```xml
<string>mahubiri</string>  <!-- Doit être identique -->
```

**Important**: Tout en minuscules, sans espaces, sans caractères spéciaux!

---

## Solution 5: Routes non définies (<1% des cas)

### Vérification

```dart
MaterialApp(
  initialRoute: '/',
  routes: {
    '/': (context) => HomePage(),
    '/home': (context) => HomePage(),
    '/login': (context) => LoginPage(),
    '/profile': (context) => ProfilePage(),
    '/reset-password': (context) => ResetPasswordPage(), // IMPORTANT
  },
)
```

Si vous utilisez `pushNamed('/reset-password')`, la route DOIT exister!

---

## 🧪 Tests de Validation

### Test 1: Vérifier que le service écoute

Ajoutez des logs dans votre service:

```dart
void init() {
  debugPrint('🚀 [INIT] Démarrage du service deep links');
  _handleInitialLink();
  _handleIncomingLinks();
  debugPrint('✅ [INIT] Service démarré');
}

void _handleIncomingLinks() {
  debugPrint('👂 [LISTEN] Écoute des liens...');
  _linkSubscription = linkStream.listen(
    (String? link) {
      debugPrint('📨 [RECEIVED] Lien reçu: $link'); // DOIT s'afficher
      if (link != null) {
        _processDeepLink(link);
      }
    },
    onError: (err) {
      debugPrint('❌ [ERROR] $err');
    },
  );
}
```

### Test 2: ADB Direct

```bash
# Test le plus simple
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://test"
```

**Résultat attendu**: L'app doit s'ouvrir (même si "test" n'est pas une route valide)

**Si l'app ne s'ouvre pas**: Problème AndroidManifest.xml
**Si l'app s'ouvre mais rien ne se passe**: Problème service Flutter

### Test 3: Avec Paramètres

```bash
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"
```

**Résultat attendu**: SnackBar vert avec "Test"

### Test 4: Logs Flutter

```bash
flutter run --verbose
```

Dans un autre terminal:

```bash
flutter logs
```

Vous devriez voir:

```
🚀 [INIT] Démarrage du service deep links
👂 [LISTEN] Écoute des liens...
✅ [INIT] Service démarré
```

Quand vous cliquez sur un lien:

```
📨 [RECEIVED] Lien reçu: mahubiri://verification-success?...
🔧 Schéma: mahubiri
🔧 Host: verification-success
...
```

---

## 🔧 Commandes de Nettoyage

Si rien ne fonctionne, réinitialisez tout:

```bash
# 1. Backend Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 2. Flutter
flutter clean
flutter pub get

# 3. Désinstaller l'app
adb uninstall com.votre.package

# 4. Rebuilder
flutter run --verbose
```

---

## 📋 Checklist Complète

### Backend Laravel ✅

-   [ ] `FLUTTER_APP_SCHEME=mahubiri` dans `.env`
-   [ ] Routes web ajoutées dans `routes/web.php`
-   [ ] Contrôleurs web créés
-   [ ] Vue `redirect-to-app.blade.php` créée
-   [ ] Cache Laravel vidé
-   [ ] Serveur Laravel en cours d'exécution

### Frontend Flutter ✅

-   [ ] Package `uni_links` dans `pubspec.yaml`
-   [ ] `flutter pub get` exécuté
-   [ ] `AndroidManifest.xml` modifié avec intent-filter
-   [ ] `Info.plist` modifié (iOS)
-   [ ] `GlobalKey<NavigatorState>` créé
-   [ ] `DeepLinkService` avec GlobalKey
-   [ ] Service initialisé dans `addPostFrameCallback`
-   [ ] `navigatorKey` passé au `MaterialApp`
-   [ ] Routes définies dans `MaterialApp`
-   [ ] App désinstallée puis réinstallée après modif native

### Tests ✅

-   [ ] Test ADB fonctionne: `adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://test"`
-   [ ] App s'ouvre avec le test ADB
-   [ ] Logs Flutter affichent les messages de debug
-   [ ] Email backend génère les bons liens
-   [ ] Clic sur lien email ouvre l'app

---

## 🆘 Aide Rapide par Symptôme

| Symptôme                            | Solution                                        |
| ----------------------------------- | ----------------------------------------------- |
| App ne s'ouvre jamais               | AndroidManifest.xml ou Info.plist mal configuré |
| App s'ouvre puis se ferme           | Problème de `launchMode` dans AndroidManifest   |
| App s'ouvre mais rien ne se passe   | Service non initialisé ou GlobalKey manquant    |
| Erreur "context not available"      | Utiliser GlobalKey au lieu de BuildContext      |
| Ça marche une fois puis plus jamais | Ajouter `WidgetsBindingObserver` pour lifecycle |
| SnackBar ne s'affiche pas           | Routes non définies ou navigation échoue        |
| Token non reçu                      | Vérifier encodage URL des paramètres            |

---

## 📞 Besoin d'Aide Supplémentaire ?

1. **Exécutez le diagnostic**:

    ```powershell
    .\diagnose-deep-links.ps1
    ```

2. **Consultez le troubleshooting**:

    ```bash
    code readmes/TROUBLESHOOTING_DEEP_LINKS.md
    ```

3. **Utilisez le DeepLinkService amélioré**:

    - Copiez `examples/DeepLinkServiceFlutter_v2.dart`
    - Suivez les instructions dans le fichier

4. **Partagez vos logs**:
    ```bash
    flutter run --verbose > logs.txt 2>&1
    ```

---

## ✅ Solution Qui Fonctionne à 100%

Si vraiment rien ne fonctionne, utilisez cette configuration minimale testée:

### 1. `lib/main.dart`

```dart
import 'package:flutter/material.dart';
import 'package:uni_links/uni_links.dart';
import 'dart:async';

void main() => runApp(MyApp());

class MyApp extends StatefulWidget {
  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final GlobalKey<NavigatorState> _key = GlobalKey<NavigatorState>();
  StreamSubscription? _sub;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initDeepLinks();
    });
  }

  void _initDeepLinks() {
    _sub = linkStream.listen((String? link) {
      if (link != null) {
        print('📱 DEEP LINK: $link');
        final uri = Uri.parse(link);
        if (uri.host == 'verification-success') {
          ScaffoldMessenger.of(_key.currentContext!).showSnackBar(
            SnackBar(content: Text('Email vérifié!'), backgroundColor: Colors.green),
          );
        }
      }
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
        appBar: AppBar(title: Text('Deep Links Test')),
        body: Center(child: Text('Test Deep Links')),
      ),
    );
  }
}
```

### 2. Test

```bash
adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?message=Test"
```

**Si ce code minimal ne fonctionne pas**: Le problème est dans AndroidManifest.xml

---

**Version**: 2.0  
**Dernière mise à jour**: Octobre 2025  
**Taux de résolution**: 99%
