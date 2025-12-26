<?php
// Script pour vérifier l'état des migrations

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Vérification des migrations ===\n\n";

// Vérifier si la table migrations existe
if (Schema::hasTable('migrations')) {
    echo "✓ Table 'migrations' existe\n\n";

    $migrations = DB::table('migrations')->get();
    echo "Nombre de migrations enregistrées : " . $migrations->count() . "\n\n";

    if ($migrations->count() > 0) {
        echo "Migrations enregistrées :\n";
        foreach ($migrations as $migration) {
            echo "  - {$migration->migration}\n";
        }
    } else {
        echo "⚠️  La table 'migrations' est VIDE\n";
        echo "   Les tables existent mais Laravel ne sait pas qu'elles ont été créées.\n";
    }
} else {
    echo "✗ Table 'migrations' n'existe PAS\n";
    echo "  Laravel ne peut pas suivre les migrations exécutées.\n";
}

echo "\n=== Tables existantes ===\n";
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = "Tables_in_" . $dbName;

foreach ($tables as $table) {
    echo "  - {$table->$key}\n";
}
