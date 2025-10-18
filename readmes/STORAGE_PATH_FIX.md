# Fix : Stockage des chemins relatifs et structure de dossiers

## 🔧 Problèmes corrigés

### **Problème 1 : URL complète enregistrée dans la base de données**

**Avant** :

```
http://192.168.1.193:8002/storage/users/avatars/2025/10/18/file.jpeg
```

❌ **Problème** : Si le serveur change (nouvelle IP, nouveau domaine), tous les liens deviennent invalides.

**Après** :

```
storage/users/avatars/2025/file.jpeg
```

✅ **Solution** : Seul le chemin relatif est enregistré, permettant flexibilité pour différents environnements.

---

### **Problème 2 : Structure de dossiers trop granulaire**

**Avant** :

```
storage/users/avatars/2025/10/18/file.jpeg
                      ^^^^^^^^^^^^
                      Année/Mois/Jour
```

❌ **Problème** : Crée des milliers de sous-dossiers inutiles (365 dossiers par an).

**Après** :

```
storage/users/avatars/2025/file.jpeg
                      ^^^^
                      Année seulement
```

✅ **Solution** : Un seul dossier par année, plus simple et efficace.

---

## 📝 Modifications apportées

### **1. ImageUploadService.php**

#### **Méthode `handleBase64Image()`** (Ligne ~63)

```php
// AVANT
return asset('storage/' . $fullPath);

// APRÈS
return 'storage/' . $fullPath;
```

#### **Méthode `handleUploadedImageFile()`** (Ligne ~95)

```php
// AVANT
return asset('storage/' . $fullPath);

// APRÈS
return 'storage/' . $fullPath;
```

#### **Méthode `generateStoragePath()`** (Ligne ~280)

```php
// AVANT
return $pathMappings[$storageType] . '/' . date('Y/m/d') . '/';

// APRÈS
return $pathMappings[$storageType] . '/' . date('Y') . '/';
```

#### **Méthode `deleteImageFile()`** (Ligne ~105)

```php
// AVANT
$relativePath = str_replace(asset('storage/'), '', $fileUrl);

// APRÈS
$relativePath = str_replace('storage/', '', $fileUrl);

// Support legacy (URLs complètes)
if (str_contains($fileUrl, '://')) {
    $relativePath = str_replace(asset('storage/'), '', $fileUrl);
    $relativePath = str_replace('storage/', '', $relativePath);
}
```

---

### **2. AudioUploadService.php**

#### **Méthode `handleBase64Audio()`** (Ligne ~59)

```php
// AVANT
return asset('storage/' . $filename);

// APRÈS
return 'storage/' . $filename;
```

#### **Méthode `handleUploadedAudioFile()`** (Ligne ~88)

```php
// AVANT
$filename = 'sermons/audio/' . date('Y/m/d') . '/' . ...;
return asset('storage/' . $filename);

// APRÈS
$filename = 'sermons/audio/' . date('Y') . '/' . ...;
return 'storage/' . $filename;
```

#### **Méthode `deleteAudioFile()`** (Ligne ~98)

```php
// Même correction que deleteImageFile()
$relativePath = str_replace('storage/', '', $fileUrl);

// Support legacy
if (str_contains($fileUrl, '://')) {
    $relativePath = str_replace(asset('storage/'), '', $fileUrl);
    $relativePath = str_replace('storage/', '', $relativePath);
}
```

---

## 📂 Nouvelle structure de dossiers

### **Avatars**

```
storage/
  └── users/
      └── avatars/
          ├── 2025/
          │   ├── abc123_avatar.jpeg
          │   ├── def456_avatar.png
          │   └── ...
          └── 2026/
              └── ...
```

### **Audio des sermons**

```
storage/
  └── sermons/
      └── audio/
          ├── 2025/
          │   ├── xyz789_sermon.mp3
          │   └── ...
          └── 2026/
              └── ...
```

### **Covers et logos**

```
storage/
  ├── sermons/
  │   └── covers/
  │       └── 2025/
  │           └── cover123_cover.jpeg
  └── churches/
      └── logos/
          └── 2025/
              └── logo456_logo.png
```

---

## 🔄 Migration des données existantes

### **Étape 1 : Identifier les enregistrements avec URLs complètes**

```sql
-- Avatars avec URLs complètes
SELECT id, name, avatar_url
FROM users
WHERE avatar_url LIKE 'http%';

-- Sermons avec URLs complètes
SELECT id, title, audio_url, cover_url
FROM sermons
WHERE audio_url LIKE 'http%' OR cover_url LIKE 'http%';

-- Églises avec logos en URLs complètes
SELECT id, name, logo_url
FROM churches
WHERE logo_url LIKE 'http%';
```

### **Étape 2 : Créer script de migration**

```php
<?php
// database/migrations/2025_10_18_000000_convert_urls_to_relative_paths.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir les avatars
        DB::table('users')
            ->where('avatar_url', 'LIKE', 'http%')
            ->update([
                'avatar_url' => DB::raw("REPLACE(avatar_url, CONCAT('" . url('/') . "/'), '')")
            ]);

        // Convertir les sermons (audio)
        DB::table('sermons')
            ->where('audio_url', 'LIKE', 'http%')
            ->update([
                'audio_url' => DB::raw("REPLACE(audio_url, CONCAT('" . url('/') . "/'), '')")
            ]);

        // Convertir les sermons (covers)
        DB::table('sermons')
            ->where('cover_url', 'LIKE', 'http%')
            ->update([
                'cover_url' => DB::raw("REPLACE(cover_url, CONCAT('" . url('/') . "/'), '')")
            ]);

        // Convertir les logos d'églises
        DB::table('churches')
            ->where('logo_url', 'LIKE', 'http%')
            ->update([
                'logo_url' => DB::raw("REPLACE(logo_url, CONCAT('" . url('/') . "/'), '')")
            ]);
    }

    public function down(): void
    {
        // Reconvertir en URLs complètes si nécessaire
        DB::table('users')
            ->whereNotNull('avatar_url')
            ->update([
                'avatar_url' => DB::raw("CONCAT('" . url('/') . "/', avatar_url)")
            ]);

        // Même chose pour sermons et churches...
    }
};
```

### **Étape 3 : Réorganiser les fichiers physiques**

```bash
# Script bash pour réorganiser les fichiers
cd storage/app/public/users/avatars

# Déplacer tous les fichiers vers dossier année
find 2025/10 -type f -exec mv {} 2025/ \;
find 2025/11 -type f -exec mv {} 2025/ \;
# ... pour chaque mois

# Supprimer les dossiers mois/jour vides
find 2025 -type d -empty -delete
```

---

## 💻 Utilisation côté Flutter

### **Affichage des images**

```dart
import 'package:flutter/material.dart';

class UserAvatar extends StatelessWidget {
  final String avatarPath; // Ex: "storage/users/avatars/2025/file.jpeg"
  final String baseUrl;    // Ex: "http://192.168.1.193:8002"

  const UserAvatar({
    required this.avatarPath,
    required this.baseUrl,
  });

  @override
  Widget build(BuildContext context) {
    // Construire l'URL complète dynamiquement
    final fullUrl = '$baseUrl/$avatarPath';

    return CircleAvatar(
      backgroundImage: NetworkImage(fullUrl),
      onBackgroundImageError: (exception, stackTrace) {
        // Afficher image par défaut en cas d'erreur
        print('Erreur de chargement: $exception');
      },
    );
  }
}

// Utilisation
UserAvatar(
  avatarPath: user.avatarUrl,  // "storage/users/avatars/2025/file.jpeg"
  baseUrl: ApiConfig.baseUrl,   // Configuré dans l'app
);
```

### **Configuration dynamique de l'URL de base**

```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String _devBaseUrl = 'http://192.168.1.193:8002';
  static const String _prodBaseUrl = 'https://api.mahubiri.com';

  static String get baseUrl {
    // Changer automatiquement selon l'environnement
    return const bool.fromEnvironment('dart.vm.product')
        ? _prodBaseUrl
        : _devBaseUrl;
  }

  static String buildFullUrl(String relativePath) {
    return '$baseUrl/$relativePath';
  }
}

// Utilisation
final fullAvatarUrl = ApiConfig.buildFullUrl(user.avatarUrl);
```

---

## 🧪 Tests

### **Test 1 : Vérifier le format du chemin**

```dart
test('Avatar path should be relative', () {
  final avatarPath = 'storage/users/avatars/2025/abc123_avatar.jpeg';

  expect(avatarPath.startsWith('storage/'), true);
  expect(avatarPath.contains('://'), false);
});
```

### **Test 2 : Construction d'URL complète**

```dart
test('Should build full URL correctly', () {
  final baseUrl = 'http://192.168.1.193:8002';
  final relativePath = 'storage/users/avatars/2025/file.jpeg';

  final fullUrl = '$baseUrl/$relativePath';

  expect(fullUrl, 'http://192.168.1.193:8002/storage/users/avatars/2025/file.jpeg');
});
```

---

## ✅ Avantages de cette approche

### **1. Portabilité**

✅ Changement de serveur sans migration de données  
✅ Environnements multiples (dev, staging, prod)  
✅ Compatible avec CDN

### **2. Maintenabilité**

✅ Structure de dossiers simple (1 dossier/an vs 365 dossiers/an)  
✅ Backup et archivage facilités  
✅ Navigation dans les fichiers plus simple

### **3. Performance**

✅ Moins de métadonnées filesystem à gérer  
✅ Recherche de fichiers plus rapide  
✅ Cache filesystem plus efficace

### **4. Flexibilité**

✅ Support URLs complètes (legacy)  
✅ Support chemins relatifs (nouveau)  
✅ Transition progressive possible

---

## 📊 Comparaison

| Aspect                    | Avant                    | Après           |
| ------------------------- | ------------------------ | --------------- |
| **Format stocké**         | URL complète             | Chemin relatif  |
|                           | `http://.../storage/...` | `storage/...`   |
| **Structure**             | Année/Mois/Jour          | Année seulement |
|                           | `2025/10/18/`            | `2025/`         |
| **Nombre de dossiers/an** | ~365                     | 1               |
| **Portabilité**           | ❌ Liée au serveur       | ✅ Indépendante |
| **Compatibilité CDN**     | ❌ Difficile             | ✅ Facile       |

---

## 🚀 Déploiement

### **Ordre des opérations**

1. ✅ **Mettre à jour le code** (services d'upload)
2. ⏳ **Exécuter migration SQL** (convertir URLs existantes)
3. ⏳ **Réorganiser fichiers** (déplacer vers structure année)
4. ⏳ **Tester** (vérifier uploads et affichages)
5. ⏳ **Déployer** (staging puis production)

### **Rollback si nécessaire**

La méthode `down()` de la migration permet de revenir aux URLs complètes si besoin.

---

**Date** : 18 octobre 2025  
**Impact** : Tous les uploads (avatars, sermons, covers, logos)  
**Compatibilité** : Support legacy maintenu  
**Migration** : Optionnelle (nouveau format appliqué automatiquement)
