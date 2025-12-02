<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file.
    | You can download this file from your Firebase project settings.
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | FCM API URL
    |--------------------------------------------------------------------------
    |
    | Firebase Cloud Messaging API URL
    |
    */
    'fcm_url' => 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID', '') . '/messages:send',
];
