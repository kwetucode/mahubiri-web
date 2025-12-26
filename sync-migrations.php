<?php
// Script pour synchroniser la table migrations avec les fichiers existants

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "=== Synchronisation des migrations ===\n\n";

// Lire tous les fichiers de migration
$migrationPath = database_path('migrations');
$migrationFiles = File::files($migrationPath);

$batch = DB::table('migrations')->max('batch') ?? 0;
$batch++; // Nouveau batch pour toutes les migrations manquantes

$inserted = 0;

foreach ($migrationFiles as $file) {
    $migrationName = str_replace('.php', '', $file->getFilename());

    // Vérifier si la migration existe déjà
    $exists = DB::table('migrations')
        ->where('migration', $migrationName)
        ->exists();

    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch
        ]);
        echo "✓ Ajouté : $migrationName\n";
        $inserted++;
    } else {
        echo "  Existe déjà : $migrationName\n";
    }
}

echo "\n=== Résumé ===\n";
echo "Migrations ajoutées : $inserted\n";
echo "Total des migrations : " . DB::table('migrations')->count() . "\n";
echo "\n✓ Vous pouvez maintenant exécuter 'php artisan migrate' sans erreur.\n";
