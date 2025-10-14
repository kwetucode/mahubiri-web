# Test de l'analyse audio avec getID3

## Fonctionnalités ajoutées

### 1. Nouveaux champs dans la table sermons

-   `mime_type` : Type MIME du fichier audio
-   `size` : Taille du fichier en octets
-   `audio_bitrate` : Bitrate de l'audio
-   `duration_formatted` : Durée formatée (ex: "03:45")
-   `audio_format` : Format du fichier audio (mp3, wav, etc.)
-   `color` : Champ pour couleur associée au sermon

### 2. Méthode d'analyse audio

La méthode `analyzeAudioFile()` utilise getID3 pour extraire :

-   Métadonnées du fichier
-   Durée en secondes et formatée
-   Bitrate et format audio
-   Taille et type MIME

### 3. Intégration dans le contrôleur

-   Analyse automatique lors de l'upload d'un fichier audio
-   Sauvegarde des métadonnées en base de données
-   Fonctionnalité disponible pour création et mise à jour

### 4. API Response enrichie

Le SermonResource inclut maintenant tous les champs d'analyse audio dans les réponses API.

## Test

Pour tester, uploadez un fichier audio via l'API `/api/v1/sermons` avec le champ `audio_file`. Les métadonnées seront automatiquement extraites et sauvegardées.

## Prérequis

La librairie `james-heinrich/getid3` doit être installée via Composer :

```bash
composer require james-heinrich/getid3
```
