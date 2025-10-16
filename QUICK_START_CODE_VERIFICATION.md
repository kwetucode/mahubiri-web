# 🎯 Guide Rapide - Système de Vérification par Code

## ✨ Qu'est-ce qui a été implémenté?

Deux systèmes de vérification par **code à 6 chiffres**:

1. **✅ Vérification Email** - Code envoyé après inscription
2. **✅ Réinitialisation Mot de Passe** - Code pour reset MDP

## 🚀 Démarrage Rapide

### 1. Configuration Email (REQUIS)

Ajoutez dans votre `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Mahubiri"
```

### 2. Migration

```bash
php artisan migrate
```

### 3. Test du Système

```bash
# Vérification complète
php verify-code-system.php

# Test email verification
powershell -ExecutionPolicy Bypass -File test-email-verification-code.ps1

# Test password reset
powershell -ExecutionPolicy Bypass -File test-password-reset-code.ps1
```

## 📋 API Endpoints

### Email Verification (Authentifié)

```bash
# 1. Envoyer code (automatique à l'inscription)
POST /api/auth/email/send-code
Authorization: Bearer {token}

# 2. Vérifier code
POST /api/auth/email/verify-code
Authorization: Bearer {token}
Body: { "code": "123456" }

# 3. Vérifier statut
GET /api/auth/email/verification-status
Authorization: Bearer {token}
```

### Password Reset (Public)

```bash
# 1. Envoyer code
POST /api/auth/password/send-code
Body: { "email": "user@example.com" }

# 2. Vérifier code (optionnel)
POST /api/auth/password/verify-code
Body: { "email": "user@example.com", "code": "123456" }

# 3. Réinitialiser
POST /api/auth/password/reset
Body: {
  "email": "user@example.com",
  "code": "123456",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

## 🔄 Flux Utilisateur

### Inscription → Vérification Email

```
1. User s'inscrit → Code envoyé automatiquement
2. User reçoit email avec code "123456"
3. User entre le code dans l'app
4. ✅ email_verified_at mis à jour
5. ✅ Email de bienvenue envoyé automatiquement
```

### Mot de Passe Oublié

```
1. User entre son email
2. User reçoit code "123456"
3. User entre code + nouveau mot de passe
4. ✅ Mot de passe changé
```

## 📧 Emails Automatiques

| Email                   | Quand                       | Contenu              |
| ----------------------- | --------------------------- | -------------------- |
| Code vérification email | Après inscription           | Code 6 chiffres      |
| Email de bienvenue      | Après vérification email ⭐ | Message de bienvenue |
| Code reset password     | Sur demande                 | Code 6 chiffres      |

## 🔐 Sécurité

-   ⏱️ Codes expirent en **15 minutes**
-   🔒 Usage **unique** (is_used = true après utilisation)
-   🔄 Nouveaux codes **invalident** les anciens
-   📊 Logs **complets** de tous les événements

## 🧹 Maintenance

### Nettoyer les codes expirés

```bash
# Manuel
php artisan codes:clean

# Automatique (cron job quotidien)
0 2 * * * cd /path/to/project && php artisan codes:clean
```

### Consulter les logs

```bash
# Email vérifié
tail -f storage/logs/laravel.log | grep "Email vérifié"

# Email de bienvenue
tail -f storage/logs/laravel.log | grep "bienvenue"

# Reset password
tail -f storage/logs/laravel.log | grep "réinitialisé"
```

## 📚 Documentation Complète

| Document                                                                                | Description                      |
| --------------------------------------------------------------------------------------- | -------------------------------- |
| [📄 IMPLEMENTATION_COMPLETE_SUMMARY.md](readmes/IMPLEMENTATION_COMPLETE_SUMMARY.md)     | **Vue d'ensemble complète** ⭐   |
| [📄 EMAIL_VERIFICATION_CODE_FLOW.md](readmes/EMAIL_VERIFICATION_CODE_FLOW.md)           | Flux email verification détaillé |
| [📄 PASSWORD_RESET_CODE_VERIFICATION.md](readmes/PASSWORD_RESET_CODE_VERIFICATION.md)   | Flux password reset détaillé     |
| [📄 COMPLETE_CODE_VERIFICATION_SYSTEM.md](readmes/COMPLETE_CODE_VERIFICATION_SYSTEM.md) | Architecture des 2 systèmes      |
| [📄 PASSWORD_RESET_TESTING.md](readmes/PASSWORD_RESET_TESTING.md)                       | Guide de test complet            |

## 🧪 Tests Rapides

### Via cURL

```bash
# Email verification
curl -X POST http://localhost:8000/api/auth/email/verify-code \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"code":"123456"}'

# Password reset
curl -X POST http://localhost:8000/api/auth/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "email":"user@example.com",
    "code":"123456",
    "password":"NewPass123!",
    "password_confirmation":"NewPass123!"
  }'
```

### Via Tinker

```bash
php artisan tinker
```

```php
// Créer un code de test
$user = User::first();
$code = UserCodeVerification::createForUser($user, 'email_verification', 15);
echo "Code: {$code->code}\n";

// Vérifier un code
$verification = UserCodeVerification::verifyCode($user->email, '123456', 'email_verification');
var_dump($verification ? 'Valide' : 'Invalide');
```

## 📱 Intégration Flutter

### Service Dart Simple

```dart
class EmailVerificationService {
  static const baseUrl = 'https://your-api.com/api/auth/email';

  Future<Map> sendCode(String token) async {
    final response = await http.post(
      Uri.parse('$baseUrl/send-code'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );
    return jsonDecode(response.body);
  }

  Future<Map> verifyCode(String token, String code) async {
    final response = await http.post(
      Uri.parse('$baseUrl/verify-code'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'code': code}),
    );
    return jsonDecode(response.body);
  }
}
```

Voir documentation complète pour exemples UI.

## ⚡ Commandes Artisan

```bash
# Nettoyer codes expirés
php artisan codes:clean

# Nettoyer avec période custom (garder 3 jours)
php artisan codes:clean --days=3

# Vérifier routes
php artisan route:list --path=email
php artisan route:list --path=password
```

## 🐛 Debugging

### Problèmes Courants

| Problème                  | Solution                       |
| ------------------------- | ------------------------------ |
| "Code invalide ou expiré" | Demander un nouveau code       |
| "Email déjà vérifié"      | Utilisateur déjà vérifié (OK)  |
| "Échec envoi email"       | Vérifier config SMTP dans .env |
| Pas d'email reçu          | Vérifier logs + spam           |

### Vérifier Configuration

```bash
# Test envoi email
php artisan tinker

Mail::raw('Test', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

## 📊 Base de Données

### Table Principale

```sql
user_code_verifications
├── code (6 chiffres)
├── type ('email_verification' ou 'password_reset')
├── expires_at (timestamp)
├── is_used (boolean)
└── used_at (timestamp nullable)
```

### Requêtes Utiles

```sql
-- Codes actifs
SELECT * FROM user_code_verifications
WHERE is_used = FALSE AND expires_at > NOW();

-- Utilisateurs non vérifiés
SELECT * FROM users WHERE email_verified_at IS NULL;

-- Statistiques
SELECT type, COUNT(*) FROM user_code_verifications GROUP BY type;
```

## ✅ Checklist de Production

-   [ ] Configuration email dans .env
-   [ ] Migration exécutée
-   [ ] Tests réussis (verify-code-system.php)
-   [ ] Emails reçus correctement
-   [ ] Logs vérifiés (aucune erreur)
-   [ ] Documentation Flutter mise à jour
-   [ ] Cron job configuré (optionnel)
-   [ ] Rate limiting activé (optionnel)

## 🎯 Points Clés

### ⭐ Fonctionnalités Principales

1. **Codes 6 chiffres** au lieu de tokens longs
2. **email_verified_at mis à jour automatiquement**
3. **Email de bienvenue automatique** après vérification
4. **Usage unique** strict pour sécurité
5. **Expiration 15 minutes**
6. **Logs complets** pour debugging

### 🚀 Avantages

-   Simple pour l'utilisateur (copier-coller)
-   Sécurisé (codes temporaires en DB)
-   Mobile-friendly (pas de deep links)
-   Maintenance facile (backend centralisé)
-   Traçabilité complète (logs + DB)

## 🆘 Besoin d'Aide?

1. **Consultez les logs**: `storage/logs/laravel.log`
2. **Vérifiez la config**: `.env` (variables MAIL\_\*)
3. **Testez le système**: `php verify-code-system.php`
4. **Lisez la doc**: `readmes/IMPLEMENTATION_COMPLETE_SUMMARY.md`

---

**✨ Système prêt! Il ne reste plus qu'à configurer l'email et tester! 🚀**

Pour commencer:

```bash
# 1. Configurez .env
# 2. Testez
php verify-code-system.php
# 3. Intégrez dans Flutter
```
