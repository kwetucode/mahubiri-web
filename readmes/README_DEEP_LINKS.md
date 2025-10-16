# 📧 Solution Deep Links - Email Verification & Password Reset

## 🎯 Vue d'ensemble

Cette solution permet aux utilisateurs de votre application Flutter de cliquer sur un lien dans leur email (vérification ou réinitialisation de mot de passe) et d'être automatiquement redirigés vers l'application mobile, sans voir de page JSON.

## 🚀 Démarrage Rapide

### Backend (Laravel) - 2 minutes

1. **Ajouter dans `.env`:**
```bash
FLUTTER_APP_SCHEME=mahubiri
```

2. **Nettoyer le cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

### Frontend (Flutter) - 5 minutes

1. **Installer le package:**
```yaml
# pubspec.yaml
dependencies:
  uni_links: ^0.5.1
```

2. **Configurer Android** (`android/app/src/main/AndroidManifest.xml`):
```xml
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="mahubiri" />
</intent-filter>
```

3. **Configurer iOS** (`ios/Runner/Info.plist`):
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

## 📚 Documentation

| Fichier | Description |
|---------|-------------|
| [QUICK_START_DEEP_LINKS.md](QUICK_START_DEEP_LINKS.md) | 📖 Guide de démarrage rapide avec checklist |
| [DEEP_LINKS_FLUTTER_SETUP.md](DEEP_LINKS_FLUTTER_SETUP.md) | 📘 Documentation technique complète |
| [EMAIL_DEEP_LINKS_SOLUTION.md](EMAIL_DEEP_LINKS_SOLUTION.md) | 📋 Architecture et détails de la solution |

## 🧪 Tester

### Méthode 1: Script PowerShell (Recommandé)
```powershell
.\test-deep-links.ps1
```
Menu interactif pour tester tous les scénarios.

### Méthode 2: Commande directe (Android)
```bash
# Test vérification email
adb shell am start -W -a android.intent.action.VIEW \
  -d "mahubiri://verification-success?status=verified&message=Test"

# Test reset password
adb shell am start -W -a android.intent.action.VIEW \
  -d "mahubiri://reset-password?token=abc123&email=test@example.com"
```

### Méthode 3: Email réel
1. Inscrivez-vous via l'API
2. Demandez un email de vérification
3. Ouvrez l'email sur votre téléphone
4. Cliquez sur le lien → L'app s'ouvre ! ✅

## 🏗️ Architecture

```
Email → Lien cliqué → Laravel Web → Page HTML → Deep Link → Flutter App
                      ↓
                   Vérifie & traite
```

## 📁 Fichiers Modifiés/Créés

### Backend (Laravel)

**Nouveaux fichiers:**
- `app/Http/Controllers/Web/EmailVerificationWebController.php`
- `app/Http/Controllers/Web/PasswordResetWebController.php`
- `resources/views/redirect-to-app.blade.php`

**Fichiers modifiés:**
- `routes/web.php` - Routes web ajoutées
- `app/Notifications/CustomVerifyEmail.php` - URL mise à jour
- `app/Notifications/CustomResetPasswordNotification.php` - URL mise à jour
- `config/app.php` - Configurations deep links ajoutées

### Frontend (Flutter)

**À créer:**
- `lib/services/deep_link_service.dart`
- `lib/pages/reset_password_page.dart`

**À modifier:**
- `lib/main.dart` - Initialiser le service de deep links
- `android/app/src/main/AndroidManifest.xml`
- `ios/Runner/Info.plist`

## 🔧 Deep Links Générés

| Action | Deep Link | Résultat |
|--------|-----------|----------|
| ✅ Email vérifié | `mahubiri://verification-success` | Affiche succès, redirige vers home |
| ⚠️ Déjà vérifié | `mahubiri://verification-success` | Affiche message, redirige vers home |
| ❌ Erreur vérification | `mahubiri://verification-failed` | Affiche erreur |
| 🔑 Reset password | `mahubiri://reset-password` | Ouvre page de reset avec token |
| ❌ Erreur reset | `mahubiri://reset-password-failed` | Affiche erreur |

## ✅ Checklist de Configuration

### Backend Laravel
- [ ] `FLUTTER_APP_SCHEME` dans `.env`
- [ ] Cache Laravel vidé
- [ ] Test email envoyé avec succès

### Frontend Flutter
- [ ] Package `uni_links` installé
- [ ] `AndroidManifest.xml` modifié
- [ ] `Info.plist` modifié
- [ ] `DeepLinkService` créé
- [ ] Routes configurées dans `MaterialApp`

### Tests
- [ ] Test ADB fonctionne
- [ ] Clic sur lien email ouvre l'app
- [ ] Vérification email fonctionne
- [ ] Reset password fonctionne

## 🐛 Dépannage Rapide

### L'app ne s'ouvre pas
1. Vérifiez que le schéma dans `.env` = schéma dans manifests
2. Désinstallez et réinstallez l'app
3. Testez avec ADB d'abord

### Page JSON s'affiche toujours
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### L'app s'ouvre mais rien ne se passe
1. Vérifiez que `DeepLinkService.init()` est appelé
2. Ajoutez des `print()` pour déboguer
3. Vérifiez les routes dans `MaterialApp`

## 📞 Support

**Documentation complète:** Consultez les fichiers dans `readmes/`

**Logs:**
- Backend: `storage/logs/laravel.log`
- Flutter: Ajoutez `print()` dans `DeepLinkService`

**Test ADB:**
```bash
adb logcat | grep -i "mahubiri"
```

## 🎉 Résultat

**Avant:** Email → Lien → JSON 😞

**Après:** Email → Lien → App s'ouvre → Action effectuée 🎉

---

**Date:** Octobre 2025 | **Version:** 1.0 | **Status:** ✅ Production Ready
