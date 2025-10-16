# Configuration de l'URL Frontend pour les Notifications

## Problème
Les liens dans les emails de réinitialisation de mot de passe pointaient vers `http://localhost:3000` au lieu de l'URL de production de l'application Flutter.

## Solution
Ajout de la variable d'environnement `FRONTEND_URL` pour configurer l'URL du frontend indépendamment de l'URL de l'API backend.

## Configuration

### 1. Fichier `.env` - Environnement LOCAL
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
```

### 2. Fichier `.env` - Environnement PRODUCTION
```env
APP_URL=https://mahubiri.mkbcentral.com
FRONTEND_URL=https://votre-app-flutter.com
```

**Note importante :** Remplacez `https://votre-app-flutter.com` par l'URL réelle de votre application Flutter en production.

### 3. Fichier `config/app.php`
La configuration a été ajoutée :
```php
'frontend_url' => env('FRONTEND_URL', env('APP_URL', 'http://localhost')),
```

## Utilisation dans les Notifications

Le fichier `app/Notifications/CustomResetPasswordNotification.php` utilise cette configuration pour générer les liens :

```php
protected function resetUrl($notifiable): string
{
    return config('app.frontend_url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
}
```

## Après modification

Après avoir modifié le fichier `.env`, exécutez :
```bash
php artisan config:clear
```

## Test

1. **En local :** Le lien de réinitialisation devrait pointer vers `http://localhost:3000/reset-password?token=...`
2. **En production :** Le lien devrait pointer vers votre URL de production Flutter `https://votre-app-flutter.com/reset-password?token=...`

## Exemples d'URLs Frontend possibles

- Application web : `https://app.mahubiri.com`
- Application mobile (deep link) : `mahubiri://reset-password` (si vous utilisez des deep links)
- Page web temporaire : `https://mahubiri.mkbcentral.com/app/reset-password`

Choisissez l'URL appropriée selon votre architecture d'application Flutter.
