# Script de Diagnostic Deep Links
# Usage: .\diagnose-deep-links.ps1

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  DIAGNOSTIC DEEP LINKS - MAHUBIRI" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Fonctions utilitaires
function Write-Check {
    param([string]$Message)
    Write-Host "🔍 $Message" -ForegroundColor Yellow
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Fail {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Cyan
}

function Write-Section {
    param([string]$Title)
    Write-Host "`n--- $Title ---" -ForegroundColor Magenta
}

# 1. Vérifier ADB
Write-Section "1. Vérification ADB"
Write-Check "Vérification de l'installation d'ADB..."

try {
    $adbVersion = & adb version 2>&1 | Select-String "Android Debug Bridge"
    if ($adbVersion) {
        Write-Success "ADB est installé: $adbVersion"
    }
} catch {
    Write-Fail "ADB n'est pas installé ou pas dans le PATH"
    Write-Info "Installez Android Platform Tools: https://developer.android.com/studio/releases/platform-tools"
}

# 2. Vérifier les devices
Write-Section "2. Devices Connectés"
Write-Check "Recherche de devices Android..."

try {
    $devices = & adb devices | Select-String "device$"
    if ($devices.Count -gt 0) {
        Write-Success "Device(s) trouvé(s):"
        & adb devices
    } else {
        Write-Fail "Aucun device Android connecté"
        Write-Info "Connectez un téléphone ou démarrez un émulateur"
    }
} catch {
    Write-Fail "Impossible de lister les devices"
}

# 3. Vérifier la configuration backend
Write-Section "3. Configuration Backend Laravel"

$envFile = ".env"
if (Test-Path $envFile) {
    Write-Success "Fichier .env trouvé"

    Write-Check "Vérification de FLUTTER_APP_SCHEME..."
    $flutterScheme = Select-String -Path $envFile -Pattern "FLUTTER_APP_SCHEME=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value }

    if ($flutterScheme) {
        Write-Success "FLUTTER_APP_SCHEME=$flutterScheme"
    } else {
        Write-Fail "FLUTTER_APP_SCHEME non défini dans .env"
        Write-Info "Ajoutez: FLUTTER_APP_SCHEME=mahubiri"
    }

    Write-Check "Vérification de APP_URL..."
    $appUrl = Select-String -Path $envFile -Pattern "APP_URL=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value }

    if ($appUrl) {
        Write-Success "APP_URL=$appUrl"
    } else {
        Write-Fail "APP_URL non défini"
    }
} else {
    Write-Fail "Fichier .env non trouvé"
    Write-Info "Créez le fichier .env à partir de .env.example"
}

# 4. Vérifier les routes web
Write-Section "4. Routes Web Laravel"

if (Test-Path "routes/web.php") {
    Write-Success "Fichier routes/web.php trouvé"

    $webRoutes = Get-Content "routes/web.php" -Raw

    if ($webRoutes -match "email/verify") {
        Write-Success "Route de vérification d'email présente"
    } else {
        Write-Fail "Route de vérification d'email manquante"
    }

    if ($webRoutes -match "reset-password") {
        Write-Success "Route de reset password présente"
    } else {
        Write-Fail "Route de reset password manquante"
    }
} else {
    Write-Fail "Fichier routes/web.php non trouvé"
}

# 5. Vérifier les contrôleurs
Write-Section "5. Contrôleurs Web"

$controllers = @(
    "app/Http/Controllers/Web/EmailVerificationWebController.php",
    "app/Http/Controllers/Web/PasswordResetWebController.php"
)

foreach ($controller in $controllers) {
    if (Test-Path $controller) {
        Write-Success "$(Split-Path $controller -Leaf) trouvé"
    } else {
        Write-Fail "$(Split-Path $controller -Leaf) manquant"
    }
}

# 6. Vérifier la vue
Write-Section "6. Vue de Redirection"

if (Test-Path "resources/views/redirect-to-app.blade.php") {
    Write-Success "Vue redirect-to-app.blade.php trouvée"
} else {
    Write-Fail "Vue redirect-to-app.blade.php manquante"
}

# 7. Test du serveur Laravel
Write-Section "7. Serveur Laravel"

Write-Check "Vérification si le serveur Laravel est actif..."

try {
    $appUrl = (Select-String -Path $envFile -Pattern "APP_URL=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value }) -replace '"', ''

    if ($appUrl) {
        $response = Invoke-WebRequest -Uri $appUrl -Method GET -TimeoutSec 5 -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            Write-Success "Serveur Laravel accessible sur $appUrl"
        }
    }
} catch {
    Write-Fail "Serveur Laravel non accessible"
    Write-Info "Démarrez le serveur: php artisan serve"
}

# 8. Suggestions de test
Write-Section "8. Tests Recommandés"

Write-Info "Pour tester manuellement:"
Write-Host ""
Write-Host "1. Test Direct Android:" -ForegroundColor Yellow
Write-Host '   adb shell am start -W -a android.intent.action.VIEW -d "mahubiri://verification-success?status=verified&message=Test"' -ForegroundColor White
Write-Host ""
Write-Host "2. Test Email Backend:" -ForegroundColor Yellow
Write-Host '   curl -X POST http://localhost:8000/api/auth/password/email -H "Content-Type: application/json" -d "{\"email\":\"test@example.com\"}"' -ForegroundColor White
Write-Host ""
Write-Host "3. Test avec le script interactif:" -ForegroundColor Yellow
Write-Host "   .\test-deep-links.ps1" -ForegroundColor White

# 9. Checklist Flutter
Write-Section "9. Checklist Flutter (à vérifier manuellement)"

Write-Info "Dans votre projet Flutter, vérifiez:"
Write-Host "  [ ] Package uni_links installé (pubspec.yaml)" -ForegroundColor White
Write-Host "  [ ] AndroidManifest.xml configuré avec intent-filter" -ForegroundColor White
Write-Host "  [ ] Info.plist configuré (iOS)" -ForegroundColor White
Write-Host "  [ ] DeepLinkService créé et initialisé" -ForegroundColor White
Write-Host "  [ ] GlobalKey<NavigatorState> utilisé" -ForegroundColor White
Write-Host "  [ ] Routes définies dans MaterialApp" -ForegroundColor White

# 10. Documentation
Write-Section "10. Documentation"

Write-Info "Guides disponibles:"
Write-Host "  - readmes/QUICK_START_DEEP_LINKS.md (démarrage rapide)" -ForegroundColor White
Write-Host "  - readmes/TROUBLESHOOTING_DEEP_LINKS.md (dépannage)" -ForegroundColor White
Write-Host "  - readmes/DEEP_LINKS_FLUTTER_SETUP.md (configuration complète)" -ForegroundColor White

# Résumé
Write-Section "RÉSUMÉ"

Write-Host ""
Write-Host "Si vous rencontrez des problèmes:" -ForegroundColor Yellow
Write-Host "1. Consultez readmes/TROUBLESHOOTING_DEEP_LINKS.md" -ForegroundColor White
Write-Host "2. Vérifiez les logs: flutter run --verbose" -ForegroundColor White
Write-Host "3. Testez avec ADB d'abord avant de tester avec email" -ForegroundColor White
Write-Host ""

Write-Host "Diagnostic terminé!" -ForegroundColor Green
Write-Host ""
