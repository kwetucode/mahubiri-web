<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\DB;

class TestNotifications extends Command
{
    protected $signature = 'test:notifications';
    protected $description = 'Test le système de notifications admin';

    public function handle()
    {
        $this->info('=== Test du système de notifications ===');
        $this->newLine();

        // 1. Vérifier la table notifications
        $this->info('1. Vérification de la table notifications...');
        try {
            $notificationCount = DB::table('notifications')->count();
            $this->info("   ✓ Table notifications existe ({$notificationCount} notifications)");
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
            $this->warn('   Exécutez: php artisan notifications:table');
            $this->warn('   Puis: php artisan migrate');
            return 1;
        }

        // 2. Vérifier les admins
        $this->newLine();
        $this->info('2. Recherche des utilisateurs admin...');
        $admins = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->get();

        if ($admins->isEmpty()) {
            $this->error('   ✗ Aucun admin trouvé !');
            $this->warn('   Assurez-vous qu\'il existe un rôle "admin" et des utilisateurs avec ce rôle');
            
            // Afficher tous les rôles
            $roles = DB::table('roles')->get();
            $this->info('   Rôles disponibles:');
            foreach ($roles as $role) {
                $userCount = DB::table('users')->where('role_id', $role->id)->count();
                $this->line("   - {$role->name} (ID: {$role->id}) - {$userCount} utilisateurs");
            }
            return 1;
        }

        $this->info("   ✓ {$admins->count()} admin(s) trouvé(s):");
        foreach ($admins as $admin) {
            $unreadCount = $admin->unreadNotifications()->count();
            $this->line("   - {$admin->name} ({$admin->email}) - {$unreadCount} non lues");
        }

        // 3. Tester l'envoi d'une notification
        $this->newLine();
        if ($this->confirm('Voulez-vous envoyer une notification de test ?', true)) {
            $testUser = User::first();
            if (!$testUser) {
                $this->error('   ✗ Aucun utilisateur dans la base de données');
                return 1;
            }

            $this->info('3. Envoi de notification de test...');
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new NewUserRegistered($testUser));
                    $this->info("   ✓ Notification envoyée à {$admin->name}");
                } catch (\Exception $e) {
                    $this->error("   ✗ Erreur pour {$admin->name}: " . $e->getMessage());
                }
            }

            // 4. Vérifier les notifications
            $this->newLine();
            $this->info('4. Vérification des notifications envoyées...');
            foreach ($admins as $admin) {
                $admin->refresh();
                $unreadCount = $admin->unreadNotifications()->count();
                $totalCount = $admin->notifications()->count();
                $this->line("   - {$admin->name}: {$totalCount} total, {$unreadCount} non lues");
            }
        }

        $this->newLine();
        $this->info('✓ Test terminé !');
        $this->info('Accédez à /test-notification (connecté) pour tester via HTTP');
        
        return 0;
    }
}
