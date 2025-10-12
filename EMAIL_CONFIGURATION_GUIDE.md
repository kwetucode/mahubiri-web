# 📧 Guide de Configuration Email - Résoudre "Email non reçu"

## 🔍 Problème actuel

**Symptôme :** L'API répond avec succès mais aucun email n'est reçu dans la boîte mail.

**Configuration actuelle dans `.env` :**

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

**Cause probable :** Aucun serveur SMTP n'écoute sur `127.0.0.1:1025` (Mailhog n'est pas démarré).

---

## 🎯 Solutions disponibles

### Solution 1 : Mailtrap (⭐ RECOMMANDÉ pour développement)

Mailtrap capture tous les emails en développement - **Gratuit et facile**.

#### Étapes :

1. **Créer un compte sur Mailtrap** :

    - Allez sur https://mailtrap.io/
    - Créez un compte gratuit
    - Créez un inbox

2. **Récupérer les identifiants** :

    - Dans votre inbox Mailtrap
    - Cliquez sur "Show Credentials"
    - Notez : Username, Password, Host, Port

3. **Mettre à jour `.env`** :

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username_mailtrap
MAIL_PASSWORD=votre_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@neno.com"
MAIL_FROM_NAME="${APP_NAME}"
FRONTEND_URL=myapp://reset-password
```

4. **Redémarrer le serveur** :

```powershell
# Arrêter le serveur actuel (Ctrl+C)
php artisan config:clear
php artisan serve --host=192.168.235.97 --port=8002
```

5. **Tester** :
    - Envoyez une demande de réinitialisation
    - Vérifiez votre inbox Mailtrap
    - L'email devrait apparaître instantanément

**✅ Avantages :**

-   Gratuit pour 500 emails/mois
-   Interface web pour voir les emails
-   Pas besoin d'installer quoi que ce soit
-   Fonctionne immédiatement
-   Permet de tester le HTML des emails

---

### Solution 2 : Mailhog (Local)

Mailhog est un serveur SMTP local qui capture les emails.

#### Étapes :

1. **Installer Mailhog** :

    **Windows (avec Chocolatey)** :

    ```powershell
    choco install mailhog
    ```

    **Ou télécharger manuellement** :

    - Téléchargez depuis https://github.com/mailhog/MailHog/releases
    - Téléchargez `MailHog_windows_amd64.exe`
    - Renommez en `mailhog.exe`
    - Placez dans `C:\mailhog\`

2. **Démarrer Mailhog** :

    ```powershell
    # Depuis le dossier où se trouve mailhog.exe
    .\mailhog.exe
    ```

    Ou si installé via Chocolatey :

    ```powershell
    mailhog
    ```

    **Mailhog démarre sur** :

    - SMTP : `localhost:1025`
    - Interface web : `http://localhost:8025`

3. **Configuration `.env`** (déjà correcte) :

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@neno.com"
MAIL_FROM_NAME="${APP_NAME}"
FRONTEND_URL=myapp://reset-password
```

4. **Redémarrer Laravel** :

```powershell
php artisan config:clear
php artisan serve --host=192.168.235.97 --port=8002
```

5. **Voir les emails** :
    - Ouvrez http://localhost:8025
    - Les emails capturés apparaîtront ici

**✅ Avantages :**

-   Totalement local (pas besoin d'internet)
-   Interface web simple
-   Gratuit et open source

**⚠️ Inconvénient :**

-   Doit être démarré manuellement à chaque fois

---

### Solution 3 : Gmail (⚠️ Pour production uniquement)

Utilisez votre compte Gmail pour envoyer des emails réels.

#### Étapes :

1. **Activer l'authentification à 2 facteurs** sur votre compte Gmail

2. **Créer un mot de passe d'application** :

    - Allez sur https://myaccount.google.com/apppasswords
    - Créez un nouveau mot de passe d'application
    - Notez le mot de passe (16 caractères)

3. **Configuration `.env`** :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre.email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="votre.email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
FRONTEND_URL=myapp://reset-password
```

4. **Redémarrer** :

```powershell
php artisan config:clear
php artisan serve --host=192.168.235.97 --port=8002
```

**⚠️ Attention :**

-   Les emails sont vraiment envoyés
-   Limite de 500 emails/jour
-   Ne pas utiliser pour des tests intensifs
-   Risque de blocage si trop d'envois

---

### Solution 4 : Mode LOG (Pour debug uniquement)

Les emails sont écrits dans les logs au lieu d'être envoyés.

#### Configuration `.env` :

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@neno.com"
MAIL_FROM_NAME="${APP_NAME}"
FRONTEND_URL=myapp://reset-password
```

#### Voir les emails :

```powershell
Get-Content storage\logs\laravel.log -Tail 100
```

**✅ Avantages :**

-   Fonctionne immédiatement
-   Aucune configuration nécessaire

**⚠️ Inconvénients :**

-   Pas d'interface visuelle
-   Difficile à lire
-   Seulement pour debug

---

## 🔧 Débogage

### 1. Vérifier que la configuration est chargée

```powershell
php artisan config:clear
php artisan tinker
```

Puis dans Tinker :

```php
config('mail.mailers.smtp');
config('mail.from.address');
exit
```

### 2. Tester l'envoi d'email manuellement

```powershell
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Password;

$user = User::where('email', 'votre@email.com')->first();
if ($user) {
    Password::sendResetLink(['email' => $user->email]);
    echo "Email envoyé !";
} else {
    echo "Utilisateur non trouvé";
}
exit
```

### 3. Vérifier les logs d'erreur

```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

Recherchez des messages contenant :

-   `Connection refused`
-   `Failed to authenticate`
-   `Could not connect to SMTP`

### 4. Vérifier la queue

Si vous utilisez `QUEUE_CONNECTION=database`, les emails sont mis en queue.

**Voir les jobs en attente** :

```powershell
php artisan queue:failed
```

**Traiter la queue** :

```powershell
php artisan queue:work --once
```

Ou pour traiter tous les jobs :

```powershell
php artisan queue:work
```

### 5. Désactiver la queue pour les emails (si nécessaire)

Si vous voulez que les emails soient envoyés immédiatement (sans queue), modifiez la notification :

**Fichier :** `app/Notifications/CustomResetPasswordNotification.php`

```php
class CustomResetPasswordNotification extends Notification // Retirer: implements ShouldQueue
{
    // Retirer: use Queueable;
```

Puis :

```powershell
php artisan config:clear
```

---

## 📝 Configuration finale recommandée

**Pour développement (Mailtrap) :**

```env
# Application
APP_NAME=Neno
APP_URL=http://192.168.235.97:8002
FRONTEND_URL=myapp://reset-password

# Email (Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username_mailtrap
MAIL_PASSWORD=votre_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@neno.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue (désactiver pour test)
QUEUE_CONNECTION=sync
```

**Pour production (Gmail ou service email) :**

```env
# Application
APP_NAME=Neno
APP_URL=https://votre-domaine.com
FRONTEND_URL=https://votre-domaine.com/reset-password

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre.email@gmail.com
MAIL_PASSWORD=mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue (activer pour performance)
QUEUE_CONNECTION=database
```

---

## ✅ Checklist de résolution

1. ✅ Choisir une solution email (Mailtrap recommandé)
2. ✅ Mettre à jour `.env` avec les bons identifiants
3. ✅ Ajouter `FRONTEND_URL=myapp://reset-password`
4. ✅ Vérifier `QUEUE_CONNECTION=sync` (pour test)
5. ✅ Exécuter `php artisan config:clear`
6. ✅ Redémarrer le serveur Laravel
7. ✅ Tester l'envoi depuis l'app Flutter
8. ✅ Vérifier la réception (Mailtrap web interface)

---

## 🚨 Problèmes courants

### "Connection refused"

**Cause :** Le serveur SMTP n'est pas accessible
**Solution :**

-   Si Mailhog : vérifier qu'il est démarré (`mailhog` dans le terminal)
-   Si Mailtrap : vérifier les identifiants
-   Vérifier le pare-feu Windows

### "Authentication failed"

**Cause :** Identifiants incorrects
**Solution :**

-   Vérifier MAIL_USERNAME et MAIL_PASSWORD
-   Pour Gmail : utiliser un mot de passe d'application

### "Email envoyé mais pas reçu"

**Cause :** Email en queue
**Solution :**

```powershell
# Voir la queue
php artisan queue:work --once

# Ou désactiver la queue
# Dans .env : QUEUE_CONNECTION=sync
```

### "Too many login attempts"

**Cause :** Gmail bloque trop de connexions
**Solution :**

-   Attendre quelques minutes
-   Utiliser Mailtrap pour les tests

---

## 💡 Recommandation finale

**Pour votre cas (développement avec Flutter) :**

1. **Utilisez Mailtrap** :

    - Gratuit, facile, pas d'installation
    - Interface web pour voir les emails
    - Parfait pour le développement

2. **Configuration minimale** :

    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=votre_username
    MAIL_PASSWORD=votre_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="noreply@neno.com"
    MAIL_FROM_NAME="Neno"
    FRONTEND_URL=myapp://reset-password
    QUEUE_CONNECTION=sync
    ```

3. **Après chaque modification** :

    ```powershell
    php artisan config:clear
    ```

4. **Tester** depuis Flutter et vérifier Mailtrap

C'est la solution la plus simple et la plus fiable pour le développement ! 🎯
