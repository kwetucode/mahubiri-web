# Script PowerShell pour tester les Deep Links
# Usage: .\test-deep-links.ps1 [verification|reset|both]

param(
    [ValidateSet('verification', 'reset', 'both')]
    [string]$TestType = 'both'
)

# Configuration
$APP_SCHEME = "mahubiri"
$ADB_PATH = "adb"

# Couleurs pour l'affichage
function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Cyan
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Write-Title {
    param([string]$Title)
    Write-Host "`n========================================" -ForegroundColor Magenta
    Write-Host "  $Title" -ForegroundColor Magenta
    Write-Host "========================================`n" -ForegroundColor Magenta
}

# Vérifier si ADB est disponible
function Test-ADB {
    try {
        $null = & $ADB_PATH version 2>&1
        return $true
    } catch {
        return $false
    }
}

# Vérifier si un device Android est connecté
function Test-AndroidDevice {
    $devices = & $ADB_PATH devices | Select-String "device$"
    return $devices.Count -gt 0
}

# Test de vérification d'email - Succès
function Test-EmailVerificationSuccess {
    Write-Title "TEST: Email Verification - Success"
    
    $deepLink = "${APP_SCHEME}://verification-success?status=verified&message=Votre email a été vérifié avec succès&user_id=1&email=test@example.com"
    
    Write-Info "Deep Link: $deepLink"
    Write-Info "Tentative d'ouverture de l'app..."
    
    if (Test-ADB -and (Test-AndroidDevice)) {
        & $ADB_PATH shell am start -W -a android.intent.action.VIEW -d "$deepLink"
        Write-Success "Commande envoyée au device Android"
    } else {
        Write-Warning "ADB non disponible ou aucun device Android connecté"
        Write-Info "Vous pouvez copier ce lien et le tester manuellement:"
        Write-Host $deepLink -ForegroundColor Yellow
    }
}

# Test de vérification d'email - Déjà vérifié
function Test-EmailVerificationAlreadyVerified {
    Write-Title "TEST: Email Verification - Already Verified"
    
    $deepLink = "${APP_SCHEME}://verification-success?status=already_verified&message=Votre email est déjà vérifié&user_id=1&email=test@example.com"
    
    Write-Info "Deep Link: $deepLink"
    Write-Info "Tentative d'ouverture de l'app..."
    
    if (Test-ADB -and (Test-AndroidDevice)) {
        & $ADB_PATH shell am start -W -a android.intent.action.VIEW -d "$deepLink"
        Write-Success "Commande envoyée au device Android"
    } else {
        Write-Warning "ADB non disponible ou aucun device Android connecté"
        Write-Info "Vous pouvez copier ce lien et le tester manuellement:"
        Write-Host $deepLink -ForegroundColor Yellow
    }
}

# Test de vérification d'email - Échec
function Test-EmailVerificationFailed {
    Write-Title "TEST: Email Verification - Failed"
    
    $deepLink = "${APP_SCHEME}://verification-failed?error=invalid_link&message=Le lien de vérification est invalide"
    
    Write-Info "Deep Link: $deepLink"
    Write-Info "Tentative d'ouverture de l'app..."
    
    if (Test-ADB -and (Test-AndroidDevice)) {
        & $ADB_PATH shell am start -W -a android.intent.action.VIEW -d "$deepLink"
        Write-Success "Commande envoyée au device Android"
    } else {
        Write-Warning "ADB non disponible ou aucun device Android connecté"
        Write-Info "Vous pouvez copier ce lien et le tester manuellement:"
        Write-Host $deepLink -ForegroundColor Yellow
    }
}

# Test de réinitialisation de mot de passe
function Test-PasswordReset {
    Write-Title "TEST: Password Reset"
    
    $token = "test_token_" + (Get-Random -Maximum 99999)
    $email = [System.Web.HttpUtility]::UrlEncode("test@example.com")
    $deepLink = "${APP_SCHEME}://reset-password?token=$token&email=$email&message=Veuillez entrer votre nouveau mot de passe"
    
    Write-Info "Deep Link: $deepLink"
    Write-Info "Token: $token"
    Write-Info "Email: test@example.com"
    Write-Info "Tentative d'ouverture de l'app..."
    
    if (Test-ADB -and (Test-AndroidDevice)) {
        & $ADB_PATH shell am start -W -a android.intent.action.VIEW -d "$deepLink"
        Write-Success "Commande envoyée au device Android"
    } else {
        Write-Warning "ADB non disponible ou aucun device Android connecté"
        Write-Info "Vous pouvez copier ce lien et le tester manuellement:"
        Write-Host $deepLink -ForegroundColor Yellow
    }
}

# Test de réinitialisation de mot de passe - Échec
function Test-PasswordResetFailed {
    Write-Title "TEST: Password Reset - Failed"
    
    $deepLink = "${APP_SCHEME}://reset-password-failed?error=missing_params&message=Lien de réinitialisation invalide"
    
    Write-Info "Deep Link: $deepLink"
    Write-Info "Tentative d'ouverture de l'app..."
    
    if (Test-ADB -and (Test-AndroidDevice)) {
        & $ADB_PATH shell am start -W -a android.intent.action.VIEW -d "$deepLink"
        Write-Success "Commande envoyée au device Android"
    } else {
        Write-Warning "ADB non disponible ou aucun device Android connecté"
        Write-Info "Vous pouvez copier ce lien et le tester manuellement:"
        Write-Host $deepLink -ForegroundColor Yellow
    }
}

# Menu interactif
function Show-Menu {
    Write-Title "Test des Deep Links - Mahubiri"
    
    Write-Host "Choisissez un test à exécuter:" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  [1] Email Verification - Success" -ForegroundColor White
    Write-Host "  [2] Email Verification - Already Verified" -ForegroundColor White
    Write-Host "  [3] Email Verification - Failed" -ForegroundColor White
    Write-Host "  [4] Password Reset" -ForegroundColor White
    Write-Host "  [5] Password Reset - Failed" -ForegroundColor White
    Write-Host "  [6] Tous les tests de vérification" -ForegroundColor White
    Write-Host "  [7] Tous les tests de reset" -ForegroundColor White
    Write-Host "  [8] Tous les tests" -ForegroundColor White
    Write-Host "  [0] Quitter" -ForegroundColor White
    Write-Host ""
    
    $choice = Read-Host "Votre choix"
    
    switch ($choice) {
        "1" { Test-EmailVerificationSuccess }
        "2" { Test-EmailVerificationAlreadyVerified }
        "3" { Test-EmailVerificationFailed }
        "4" { Test-PasswordReset }
        "5" { Test-PasswordResetFailed }
        "6" { 
            Test-EmailVerificationSuccess
            Start-Sleep -Seconds 2
            Test-EmailVerificationAlreadyVerified
            Start-Sleep -Seconds 2
            Test-EmailVerificationFailed
        }
        "7" { 
            Test-PasswordReset
            Start-Sleep -Seconds 2
            Test-PasswordResetFailed
        }
        "8" { 
            Test-EmailVerificationSuccess
            Start-Sleep -Seconds 2
            Test-EmailVerificationAlreadyVerified
            Start-Sleep -Seconds 2
            Test-EmailVerificationFailed
            Start-Sleep -Seconds 2
            Test-PasswordReset
            Start-Sleep -Seconds 2
            Test-PasswordResetFailed
        }
        "0" { 
            Write-Info "Au revoir!"
            return
        }
        default { 
            Write-Error "Choix invalide"
            Start-Sleep -Seconds 1
            Show-Menu
        }
    }
    
    Write-Host ""
    Write-Info "Test terminé. Appuyez sur une touche pour revenir au menu..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    Show-Menu
}

# Afficher les informations système
function Show-SystemInfo {
    Write-Title "Informations Système"
    
    # Vérifier ADB
    if (Test-ADB) {
        Write-Success "ADB est installé et accessible"
        $adbVersion = & $ADB_PATH version | Select-String "Version"
        Write-Info $adbVersion
        
        # Vérifier les devices
        if (Test-AndroidDevice) {
            Write-Success "Device Android connecté:"
            & $ADB_PATH devices
        } else {
            Write-Warning "Aucun device Android connecté"
            Write-Info "Connectez un device ou démarrez un émulateur"
        }
    } else {
        Write-Warning "ADB n'est pas installé ou n'est pas dans le PATH"
        Write-Info "Pour installer ADB:"
        Write-Info "  - Téléchargez Android Platform Tools"
        Write-Info "  - Ou installez Android Studio"
    }
    
    Write-Host ""
    Write-Info "Schéma de l'app: $APP_SCHEME"
    Write-Host ""
}

# Fonction principale
function Main {
    # Charger System.Web pour UrlEncode
    Add-Type -AssemblyName System.Web
    
    # Afficher les infos système
    Show-SystemInfo
    
    # Exécuter selon le paramètre
    if ($TestType -eq 'verification') {
        Test-EmailVerificationSuccess
        Start-Sleep -Seconds 2
        Test-EmailVerificationAlreadyVerified
        Start-Sleep -Seconds 2
        Test-EmailVerificationFailed
    } elseif ($TestType -eq 'reset') {
        Test-PasswordReset
        Start-Sleep -Seconds 2
        Test-PasswordResetFailed
    } elseif ($TestType -eq 'both') {
        # Si aucun paramètre, afficher le menu
        if ($PSBoundParameters.Count -eq 0) {
            Show-Menu
        } else {
            Test-EmailVerificationSuccess
            Start-Sleep -Seconds 2
            Test-EmailVerificationAlreadyVerified
            Start-Sleep -Seconds 2
            Test-EmailVerificationFailed
            Start-Sleep -Seconds 2
            Test-PasswordReset
            Start-Sleep -Seconds 2
            Test-PasswordResetFailed
        }
    }
}

# Exécuter
Main
