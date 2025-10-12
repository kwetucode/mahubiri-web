# Script PowerShell pour corriger les routes d'authentification dans les tests
# Remplace /api/auth par /api/v1/auth dans tous les fichiers de tests

Write-Host "Correction des routes d'authentification dans les tests..." -ForegroundColor Green

# Fichiers a corriger
$files = @(
    "tests\Feature\Auth\RegisterControllerTest.php",
    "tests\Feature\Auth\LoginControllerTest.php", 
    "tests\Feature\Auth\EmailVerificationControllerTest.php",
    "tests\Feature\Auth\AuthenticationIntegrationTest.php"
)

# Compteur de corrections
$count = 0

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Correction de $file..." -ForegroundColor Yellow
        
        # Lire le contenu du fichier
        $content = Get-Content $file -Raw
        
        # Remplacer /api/auth par /api/v1/auth
        $newContent = $content -replace '/api/auth', '/api/v1/auth'
        
        # Ecrire le nouveau contenu
        $newContent | Set-Content $file -NoNewline
        
        $count++
        Write-Host "Fichier $file corrige" -ForegroundColor Green
    }
    else {
        Write-Host "Fichier non trouve: $file" -ForegroundColor Red
    }
}

Write-Host "Correction terminee. $count fichiers traites." -ForegroundColor Green
Write-Host "Vous pouvez maintenant executer les tests avec: vendor\bin\phpunit" -ForegroundColor Cyan