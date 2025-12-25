<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SermonView;
use Illuminate\Support\Facades\DB;

echo "=================================================================\n";
echo "Nettoyage des doublons dans sermon_views\n";
echo "=================================================================\n\n";

try {
    DB::beginTransaction();

    // Obtenir tous les doublons groupés par user_id et sermon_id
    $duplicates = DB::table('sermon_views')
        ->select('user_id', 'sermon_id', DB::raw('COUNT(*) as count'))
        ->groupBy('user_id', 'sermon_id')
        ->having('count', '>', 1)
        ->get();

    echo "Groupes de doublons trouvés: " . $duplicates->count() . "\n\n";

    $totalDeleted = 0;
    $groupsProcessed = 0;

    foreach ($duplicates as $duplicate) {
        $userId = $duplicate->user_id;
        $sermonId = $duplicate->sermon_id;
        $count = $duplicate->count;

        echo "Traitement: User #{$userId} - Sermon #{$sermonId} ({$count} enregistrements)\n";

        // Récupérer tous les enregistrements pour ce groupe
        $records = SermonView::where('user_id', $userId)
            ->where('sermon_id', $sermonId)
            ->orderBy('duration_played', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($records->isEmpty()) {
            continue;
        }

        // Garder le premier (durée max + date récente)
        $keepRecord = $records->first();
        
        // Vérifier si au moins un enregistrement est marqué comme complété
        $anyCompleted = $records->contains('completed', true);
        
        // Mettre à jour l'enregistrement à garder avec les meilleures valeurs
        $keepRecord->update([
            'duration_played' => $records->max('duration_played'),
            'completed' => $anyCompleted,
            'played_at' => $records->max('played_at'),
        ]);

        echo "  ✓ Gardé: ID #{$keepRecord->id} - Durée: {$keepRecord->duration_played}s - Complété: " . ($keepRecord->completed ? 'Oui' : 'Non') . "\n";

        // Supprimer tous les autres enregistrements
        $idsToDelete = $records->skip(1)->pluck('id')->toArray();
        
        if (!empty($idsToDelete)) {
            $deleted = SermonView::whereIn('id', $idsToDelete)->delete();
            $totalDeleted += $deleted;
            echo "  ✗ Supprimé: {$deleted} doublons\n";
        }

        $groupsProcessed++;
        echo "\n";
    }

    echo "=================================================================\n";
    echo "RÉSUMÉ\n";
    echo "=================================================================\n";
    echo "Groupes traités: {$groupsProcessed}\n";
    echo "Enregistrements supprimés: {$totalDeleted}\n";
    echo "Enregistrements conservés: {$groupsProcessed}\n";

    // Afficher les statistiques après nettoyage
    echo "\n=================================================================\n";
    echo "STATISTIQUES APRÈS NETTOYAGE\n";
    echo "=================================================================\n";
    
    $totalRecords = SermonView::count();
    $uniqueUsers = SermonView::distinct('user_id')->count('user_id');
    $uniqueSermons = SermonView::distinct('sermon_id')->count('sermon_id');
    
    echo "Total d'enregistrements: {$totalRecords}\n";
    echo "Utilisateurs uniques: {$uniqueUsers}\n";
    echo "Sermons uniques: {$uniqueSermons}\n";

    DB::commit();
    
    echo "\n✅ Nettoyage terminé avec succès!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
