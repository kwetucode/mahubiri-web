# NENO-BACK - API de Gestion d'Églises et Sermons

## Vue d'ensemble du projet

NENO-BACK est une API REST complète construite avec Laravel 10 pour la gestion d'églises et de sermons audio. Cette API permet aux utilisateurs de créer et gérer leurs églises, puis de publier des prédications audio dans leur église.

## Architecture du système

### Technologies utilisées

-   **Framework** : Laravel 10
-   **Authentification** : Laravel Sanctum
-   **Base de données** : SQLite (développement), MySQL/PostgreSQL (production)
-   **Tests** : PHPUnit avec RefreshDatabase
-   **Upload de fichiers** : Base64 encoding avec Storage Laravel

### Structure des modules

#### 1. Système d'authentification

-   Inscription/Connexion des utilisateurs
-   Gestion des rôles utilisateur
-   Authentification par tokens API (Sanctum)
-   Vérification d'email et réinitialisation de mot de passe

#### 2. Module Churches (Églises)

-   **Fonctionnalités** :
    -   Création d'église (une par utilisateur maximum)
    -   Upload de logo en base64
    -   CRUD complet avec sécurité
    -   API RESTful avec validation
-   **Endpoints** : `/api/churches`
-   **Documentation** : `CHURCH_CONTROLLER.md`
-   **Tests** : 13 tests (5 feature + 8 unit tests)

#### 3. Module Sermons (Prédications)

-   **Fonctionnalités** :
    -   Publication de sermons audio (MP3)
    -   Upload d'image de couverture
    -   Gestion de la durée et métadonnées
    -   Association automatique à l'église de l'utilisateur
-   **Endpoints** : `/api/sermons`
-   **Documentation** : `SERMON_API.md`
-   **Tests** : 2 tests feature

## Installation et configuration

### Prérequis

-   PHP 8.1 ou supérieur
-   Composer
-   SQLite/MySQL/PostgreSQL
-   Extensions PHP : fileinfo, mbstring, openssl, PDO, Tokenizer, XML

### Installation

```bash
# Cloner et installer les dépendances
git clone [repository-url]
cd neno-back
composer install

# Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# Configuration de la base de données
# Éditer .env avec vos paramètres de BDD

# Migrations et seed
php artisan migrate --seed

# Génération du lien symbolique pour le stockage
php artisan storage:link

# Démarrage du serveur
php artisan serve --port=8000
```

### Configuration des fichiers

Le système de stockage utilise `storage/app/public/` :

-   Images d'églises : `churches/logos/`
-   Audio des sermons : `sermons/audio/`
-   Couvertures des sermons : `sermons/covers/`

## Guide d'utilisation API

### Authentification

Toutes les routes API (sauf login/register) nécessitent un token Sanctum :

```bash
# Obtenir un token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'

# Utiliser le token dans les requêtes
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/churches
```

### Workflow complet

#### 1. Créer un compte et se connecter

```bash
# Inscription
POST /api/auth/register
{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "password": "password",
  "password_confirmation": "password",
  "phone": "+243123456789"
}

# Connexion
POST /api/auth/login
{
  "email": "jean@example.com",
  "password": "password"
}
```

#### 2. Créer son église

```bash
POST /api/churches
{
  "name": "Église Évangélique de Kinshasa",
  "abbreviation": "EEK",
  "description": "Une église au cœur de la ville",
  "logo_base64": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

#### 3. Publier des sermons

```bash
POST /api/sermons
{
  "title": "La grâce de Dieu",
  "preacher_name": "Pasteur Jean",
  "description": "Un message sur la grâce",
  "audio_base64": "data:audio/mp3;base64,SUQzBAA...",
  "cover_base64": "data:image/jpeg;base64,/9j/4AAQ...",
  "duration": 1800,
  "church_id": 1
}
```

## Tests et qualité

### Tests automatisés

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test tests/Feature/ChurchControllerTest.php
php artisan test tests/Feature/SermonControllerTest.php
php artisan test tests/Unit/Requests/StoreChurchRequestTest.php
```

### Couverture des tests

-   **ChurchController** : 5 tests feature
-   **SermonController** : 2 tests feature
-   **Validation** : 8 tests unitaires
-   **Total** : 15 tests avec 100% de succès

### Validation des données

-   Messages d'erreur en français
-   Validation complète des formats base64
-   Contrôles de sécurité et d'autorisation
-   Sanitisation des données d'entrée

## Sécurité

### Mesures de sécurité implémentées

1. **Authentification obligatoire** pour toutes les routes protégées
2. **Autorisation par propriété** : seuls les propriétaires peuvent modifier leurs ressources
3. **Validation stricte** des formats de fichiers et données
4. **Une église par utilisateur** maximum
5. **Noms de fichiers uniques** pour éviter les conflits
6. **Sanitisation** des entrées utilisateur

### Headers de sécurité recommandés

```nginx
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header X-XSS-Protection "1; mode=block";
```

## Modèles de données

### Relations

```
User (1) ←→ (1) Church ←→ (n) Sermon
User (n) ←→ (1) Role
```

### Contraintes

-   Un utilisateur ne peut créer qu'une seule église
-   Seuls les propriétaires d'église peuvent créer des sermons
-   Suppression en cascade : Church → Sermons

## API Reference

### Churches

| Méthode | Endpoint                  | Description                      |
| ------- | ------------------------- | -------------------------------- |
| GET     | `/api/churches`           | Liste toutes les églises         |
| POST    | `/api/churches`           | Crée une nouvelle église         |
| GET     | `/api/churches/{id}`      | Détails d'une église             |
| PUT     | `/api/churches/{id}`      | Met à jour une église            |
| DELETE  | `/api/churches/{id}`      | Supprime une église              |
| GET     | `/api/churches/my-church` | Église de l'utilisateur connecté |

### Sermons

| Méthode | Endpoint                         | Description                          |
| ------- | -------------------------------- | ------------------------------------ |
| GET     | `/api/sermons`                   | Liste tous les sermons               |
| POST    | `/api/sermons`                   | Crée un nouveau sermon               |
| GET     | `/api/sermons/{id}`              | Détails d'un sermon                  |
| PUT     | `/api/sermons/{id}`              | Met à jour un sermon                 |
| DELETE  | `/api/sermons/{id}`              | Supprime un sermon                   |
| GET     | `/api/sermons/my-church-sermons` | Sermons de l'église de l'utilisateur |

## Déploiement

### Configuration de production

1. **Variables d'environnement** :

    ```env
    APP_ENV=production
    APP_DEBUG=false
    DB_CONNECTION=mysql
    SANCTUM_STATEFUL_DOMAINS=yourdomain.com
    ```

2. **Optimisations** :

    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    composer install --optimize-autoloader --no-dev
    ```

3. **Permissions** :
    ```bash
    chmod -R 755 storage/
    chmod -R 755 bootstrap/cache/
    ```

## Support et maintenance

### Logs

Les logs sont disponibles dans `storage/logs/laravel.log`

### Debugging

En cas de problème, activer le debug :

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Mise à jour

```bash
composer update
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

## Contribuer

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## License

Ce projet est sous licence [MIT](LICENSE).

## Contact

Pour toute question ou support, contactez l'équipe de développement.

---

**Documentation détaillée :**

-   [Church API Documentation](CHURCH_CONTROLLER.md)
-   [Sermon API Documentation](SERMON_API.md)
-   [Authentication Guide](AUTHENTICATION.md)
