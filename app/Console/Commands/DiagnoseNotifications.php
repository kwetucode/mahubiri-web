<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\UserNotificationSettings;
use Illuminate\Console\Command;

class DiagnoseNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose the notification system configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnosing Notification System...');
        $this->newLine();

        // Check Firebase Configuration
        $this->info('📋 Firebase Configuration:');
        $credentialsPath = config('firebase.credentials');
        $projectId = config('firebase.project_id');

        if (file_exists($credentialsPath)) {
            $this->info("  ✅ Firebase credentials file exists: {$credentialsPath}");
        } else {
            $this->error("  ❌ Firebase credentials file NOT found: {$credentialsPath}");
        }

        if ($projectId) {
            $this->info("  ✅ Firebase Project ID configured: {$projectId}");
        } else {
            $this->error("  ❌ Firebase Project ID NOT configured");
        }

        $this->newLine();

        // Check FCM Tokens
        $this->info('📱 FCM Tokens:');
        $totalTokens = UserFcmToken::count();
        $uniqueUsers = UserFcmToken::distinct()->count('user_id');

        $this->info("  Total FCM tokens: {$totalTokens}");
        $this->info("  Users with tokens: {$uniqueUsers}");

        if ($totalTokens === 0) {
            $this->warn("  ⚠️  No FCM tokens registered. Users need to register their tokens from the mobile app.");
        }

        $this->newLine();

        // Check Notification Settings
        $this->info('⚙️  Notification Settings:');
        $totalSettings = UserNotificationSettings::count();
        $usersWithSermonEnabled = UserNotificationSettings::where('new_sermon', true)
            ->where('push_enabled', true)
            ->count();

        $this->info("  Users with notification settings: {$totalSettings}");
        $this->info("  Users with sermon notifications enabled: {$usersWithSermonEnabled}");

        if ($totalSettings === 0) {
            $this->warn("  ⚠️  No notification settings configured. Default settings will be used.");
        }

        $this->newLine();

        // Check Users
        $this->info('👥 Users:');
        $totalUsers = User::count();
        $this->info("  Total users: {$totalUsers}");

        if ($totalUsers === 0) {
            $this->error("  ❌ No users in the system");
        }

        $this->newLine();

        // Summary
        $this->info('📊 Summary:');
        $allGood = file_exists($credentialsPath) && $projectId && $totalTokens > 0;

        if ($allGood) {
            $this->info("  ✅ System appears to be configured correctly!");
            $this->info("  ✅ Ready to send notifications to {$uniqueUsers} users");
        } else {
            $this->warn("  ⚠️  Issues detected:");
            if (!file_exists($credentialsPath)) {
                $this->line("     - Firebase credentials file missing");
            }
            if (!$projectId) {
                $this->line("     - Firebase Project ID not configured");
            }
            if ($totalTokens === 0) {
                $this->line("     - No FCM tokens registered (users need to register from the app)");
            }
        }

        $this->newLine();
        $this->info('💡 Next Steps:');
        $this->line('  1. Ensure users register their FCM tokens from the mobile app');
        $this->line('  2. Use the test route: POST /api/test-notification (with auth token)');
        $this->line('  3. Check logs: storage/logs/laravel.log');

        return 0;
    }
}
