# 🎵 SermonController - Support des Fichiers et Base64

## 📋 Fonctionnalités Améliorées

Le `SermonController` prend maintenant en charge **deux types d'upload** :

-   ✅ **Base64** (comme avant)
-   ✅ **Fichiers uploadés** (nouveau)

---

## 🚀 Utilisation des Endpoints

### 1. **Créer un Sermon** - `POST /api/sermons`

#### **Option A : Upload avec Base64**

```json
{
    "title": "Sermon du dimanche",
    "preacher_name": "Pasteur Martin",
    "description": "Un message inspirant sur la foi",
    "duration": 3600,
    "church_id": 1,
    "audio_base64": "data:audio/mp3;base64,UklGRiQAAABXQVZF...",
    "cover_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA..."
}
```

#### **Option B : Upload avec Fichiers**

```javascript
// FormData pour upload de fichiers
const formData = new FormData();
formData.append("title", "Sermon du dimanche");
formData.append("preacher_name", "Pasteur Martin");
formData.append("description", "Un message inspirant sur la foi");
formData.append("duration", "3600");
formData.append("church_id", "1");
formData.append("audio_file", audioFileInput.files[0]); // Fichier audio
formData.append("cover_file", coverFileInput.files[0]); // Fichier image

fetch("/api/sermons", {
    method: "POST",
    headers: {
        Authorization: "Bearer " + token,
        // Ne pas inclure 'Content-Type' pour FormData
    },
    body: formData,
});
```

#### **Option C : Mixte (Base64 + Fichier)**

```javascript
const formData = new FormData();
formData.append("title", "Sermon du dimanche");
formData.append("preacher_name", "Pasteur Martin");
formData.append("audio_file", audioFileInput.files[0]); // Fichier
formData.append("cover_base64", "data:image/jpeg;base64,..."); // Base64
```

---

### 2. **Modifier un Sermon** - `PUT /api/sermons/{id}`

Les mêmes options sont disponibles pour la modification :

#### **Modification Partielle avec Fichier**

```javascript
const formData = new FormData();
formData.append("title", "Nouveau titre");
formData.append("audio_file", newAudioFile); // Remplace l'ancien audio
// Les autres champs restent inchangés
```

---

## 🔧 Formats Supportés

### **Audio** 📀

-   **Formats** : `mp3`, `wav`, `m4a`, `aac`, `ogg`
-   **Taille max** : `50 MB`
-   **Champs** : `audio_base64` OU `audio_file`

### **Images de Couverture** 🖼️

-   **Formats** : `jpeg`, `jpg`, `png`, `gif`, `webp`
-   **Taille max** : `10 MB`
-   **Champs** : `cover_base64` OU `cover_file`

---

## ✅ Règles de Validation

### **StoreSermonRequest** (Création)

```php
// Au moins un type d'audio (base64 OU fichier)
'audio_base64' => 'required_without:audio_file',
'audio_file' => 'required_without:audio_base64',

// Au moins un type de cover (base64 OU fichier)
'cover_base64' => 'required_without:cover_file',
'cover_file' => 'required_without:cover_base64',
```

### **UpdateSermonRequest** (Modification)

```php
// Tous les champs sont optionnels pour les updates
'audio_base64' => 'sometimes|nullable',
'audio_file' => 'sometimes|nullable',
'cover_base64' => 'sometimes|nullable',
'cover_file' => 'sometimes|nullable',
```

---

## 🗂️ Organisation des Fichiers

### **Structure de Stockage :**

```
storage/app/public/
├── sermons/          # Fichiers audio
│   ├── audio_123456789.mp3
│   └── audio_987654321.wav
├── covers/           # Images de couverture
│   ├── cover_123456789.jpg
│   └── cover_987654321.png
└── images/           # Images génériques
    └── ...
```

### **URLs Générées :**

```php
// Audio
'audio_url' => 'storage/sermons/audio_1697123456.mp3'

// Cover
'cover_url' => 'storage/covers/cover_1697123456.jpg'
```

---

## 📝 Exemples Pratiques

### **Frontend React/Vue.js**

```javascript
// Component pour upload de sermon
function SermonUploadForm() {
    const [formData, setFormData] = useState({
        title: "",
        preacher_name: "",
        description: "",
        church_id: 1,
    });

    const [audioFile, setAudioFile] = useState(null);
    const [coverFile, setCoverFile] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();

        const data = new FormData();
        Object.keys(formData).forEach((key) => {
            data.append(key, formData[key]);
        });

        if (audioFile) data.append("audio_file", audioFile);
        if (coverFile) data.append("cover_file", coverFile);

        try {
            const response = await fetch("/api/sermons", {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
                body: data,
            });

            const result = await response.json();
            console.log("Sermon créé:", result);
        } catch (error) {
            console.error("Erreur:", error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="text"
                placeholder="Titre"
                value={formData.title}
                onChange={(e) =>
                    setFormData({ ...formData, title: e.target.value })
                }
            />

            <input
                type="file"
                accept="audio/*"
                onChange={(e) => setAudioFile(e.target.files[0])}
            />

            <input
                type="file"
                accept="image/*"
                onChange={(e) => setCoverFile(e.target.files[0])}
            />

            <button type="submit">Créer Sermon</button>
        </form>
    );
}
```

### **Backend PHP (Alternative)**

```php
// Si vous voulez traiter directement les fichiers
public function store(Request $request)
{
    $request->validate([
        'audio_file' => 'required|file|mimes:mp3,wav|max:51200',
        'cover_file' => 'nullable|file|mimes:jpeg,png|max:10240',
        // ... autres validations
    ]);

    $audioPath = $request->file('audio_file')->store('sermons', 'public');
    $coverPath = $request->file('cover_file')?->store('covers', 'public');

    $sermon = Sermon::create([
        'audio_url' => $audioPath,
        'cover_url' => $coverPath,
        // ... autres données
    ]);
}
```

---

## 🔄 Gestion des Fichiers Existants

### **Remplacement Automatique**

```php
// Dans handleFileUploads()
if ($existingSermon && $existingSermon->audio_url) {
    // L'ancien fichier est automatiquement supprimé
    $this->uploadService->deleteFile($existingSermon->audio_url, 'audio');
}

// Le nouveau fichier remplace l'ancien
$validated['audio_url'] = $this->uploadService->handleAudioUpload($newFile);
```

### **Suppression lors de la Suppression du Sermon**

```php
// Dans destroy()
$this->deleteSermonFiles($sermon); // Supprime tous les fichiers
$sermon->delete(); // Puis supprime l'enregistrement
```

---

## 📊 Avantages de l'Implémentation

### ✅ **Flexibilité Maximale**

-   Support des deux types d'upload (base64 + fichiers)
-   Possibilité de mixer les types dans une même requête
-   Validation adaptée selon le type

### ✅ **Gestion Optimisée**

-   Noms de fichiers uniques avec timestamp
-   Organisation claire des dossiers
-   Suppression automatique des anciens fichiers

### ✅ **Validation Robuste**

-   Vérification des formats de fichiers
-   Limites de taille appropriées
-   Messages d'erreur en français

### ✅ **Maintenance Facile**

-   Code centralisé dans UploadSermonService
-   Gestion d'erreurs standardisée
-   Tests unitaires possibles

---

## 🧪 Tests avec Curl

### **Test avec Fichiers**

```bash
curl -X POST http://localhost:8000/api/sermons \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Test Sermon" \
  -F "preacher_name=Test Preacher" \
  -F "church_id=1" \
  -F "audio_file=@/path/to/audio.mp3" \
  -F "cover_file=@/path/to/cover.jpg"
```

### **Test avec Base64**

```bash
curl -X POST http://localhost:8000/api/sermons \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Sermon",
    "preacher_name": "Test Preacher",
    "church_id": 1,
    "audio_base64": "data:audio/mp3;base64,...",
    "cover_base64": "data:image/jpeg;base64,..."
  }'
```

---

**✨ Le SermonController supporte maintenant tous les types d'upload pour une flexibilité maximale !**

-   ✅ **Base64** - Pour les applications web/mobile
-   ✅ **Fichiers** - Pour les formulaires HTML classiques
-   ✅ **Mixte** - Combinaison des deux selon les besoins
-   ✅ **Validation complète** - Formats et tailles vérifiés
-   ✅ **Gestion automatique** - Suppression et remplacement des fichiers
