<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Auth;

Route::get('/test-notification', function () {
    // Get current authenticated user (should be admin)
    $currentUser = Auth::user();
    
    if (!$currentUser) {
        return response()->json([
            'error' => 'Not authenticated'
        ], 401);
    }

    // Create a test user
    $testUser = User::first();
    
    if (!$testUser) {
        return response()->json([
            'error' => 'No users found in database'
        ]);
    }

    // Send notification to current user
    $currentUser->notify(new NewUserRegistered($testUser));

    return response()->json([
        'success' => true,
        'message' => 'Test notification sent',
        'notification_sent_to' => [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
            'email' => $currentUser->email,
        ],
        'notification_about' => [
            'id' => $testUser->id,
            'name' => $testUser->name,
            'email' => $testUser->email,
        ],
        'unread_count' => $currentUser->unreadNotifications()->count(),
        'latest_notifications' => $currentUser->notifications()->latest()->take(3)->get()
    ]);
})->middleware('auth:sanctum');
