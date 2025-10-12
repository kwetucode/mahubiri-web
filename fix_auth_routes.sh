#!/bin/bash

# Script pour corriger les routes d'authentification dans les tests
# Remplace /api/auth par /api/v1/auth dans tous les fichiers de tests

echo "Correction des routes d'authentification dans les tests..."

# Fichiers à corriger
files=(
    "tests/Feature/Auth/RegisterControllerTest.php"
    "tests/Feature/Auth/LoginControllerTest.php" 
    "tests/Feature/Auth/EmailVerificationControllerTest.php"
    "tests/Feature/Auth/AuthenticationIntegrationTest.php"
)

# Compteur de corrections
count=0

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "Correction de $file..."
        # Remplacement de /api/auth par /api/v1/auth
        sed -i 's|/api/auth|/api/v1/auth|g' "$file"
        ((count++))
    else
        echo "Fichier non trouvé: $file"
    fi
done

echo "Correction terminée. $count fichiers traités."
echo "Vous pouvez maintenant exécuter les tests avec: php artisan test"