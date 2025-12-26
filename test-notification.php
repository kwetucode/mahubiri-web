<?php

use App\Models\User;
use App\Notifications\WelcomeNotification;

// Get first user
$user = User::first();

if ($user) {
    // Send a test notification
    $user->notify(new WelcomeNotification($user));
    echo "✅ Notification envoyée à {$user->name} ({$user->email})\n";
} else {
    echo "❌ Aucun utilisateur trouvé dans la base de données\n";
}
