<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Vérification des données pour l'utilisateur ID: 2\n";
echo str_repeat("=", 60) . "\n\n";

// Compter les enregistrements
$count = DB::table('sermon_views')->where('user_id', 2)->count();
echo "Total d'enregistrements sermon_views: $count\n\n";

// Récupérer les données
$views = DB::table('sermon_views')
    ->where('user_id', 2)
    ->select('id', 'sermon_id', 'duration_played', 'completed', 'played_at')
    ->get();

echo "Détails des enregistrements:\n";
echo str_repeat("-", 60) . "\n";

$totalDuration = 0;
foreach ($views as $view) {
    echo "ID: {$view->id} | Sermon: {$view->sermon_id} | ";
    echo "Durée: " . ($view->duration_played ?? 'NULL') . "s | ";
    echo "Complété: " . ($view->completed ? 'Oui' : 'Non') . " | ";
    echo "Date: {$view->played_at}\n";
    
    if ($view->duration_played) {
        $totalDuration += $view->duration_played;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "TOTAL DURÉE: $totalDuration secondes\n";
echo "TOTAL DURÉE: " . round($totalDuration / 3600, 2) . " heures\n";

// Vérifier les sermons distincts
$distinctSermons = DB::table('sermon_views')
    ->where('user_id', 2)
    ->distinct()
    ->count('sermon_id');
echo "Sermons distincts écoutés: $distinctSermons\n";

// Obtenir les durées des sermons
echo "\n" . str_repeat("=", 60) . "\n";
echo "Durées réelles des sermons:\n";
echo str_repeat("-", 60) . "\n";

$sermonIds = DB::table('sermon_views')
    ->where('user_id', 2)
    ->distinct()
    ->pluck('sermon_id');

foreach ($sermonIds as $sermonId) {
    $sermon = DB::table('sermons')
        ->where('id', $sermonId)
        ->select('id', 'title', 'duration', 'duration_formatted')
        ->first();
    
    if ($sermon) {
        echo "Sermon #{$sermon->id}: {$sermon->title}\n";
        echo "  Durée: {$sermon->duration}s ({$sermon->duration_formatted})\n";
        
        // Compter combien de fois ce sermon a été enregistré
        $playCount = DB::table('sermon_views')
            ->where('user_id', 2)
            ->where('sermon_id', $sermonId)
            ->count();
        
        echo "  Nombre d'enregistrements: $playCount\n\n";
    }
}
