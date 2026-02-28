<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shwary API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Shwary Mobile Money payment gateway.
    | Supports DRC (Congo), Kenya, and Uganda.
    |
    */

    'base_url' => env('SHWARY_BASE_URL', 'https://api.shwary.com/api/v1'),

    'merchant_id' => env('SHWARY_MERCHANT_ID'),

    'merchant_key' => env('SHWARY_MERCHANT_KEY'),

    'sandbox' => env('SHWARY_SANDBOX', true),

    'default_country' => env('SHWARY_DEFAULT_COUNTRY', 'DRC'),

    /*
    |--------------------------------------------------------------------------
    | Supported Countries
    |--------------------------------------------------------------------------
    |
    | Country codes and their configurations.
    |
    */
    'countries' => [
        'DRC' => [
            'name' => 'République Démocratique du Congo',
            'phone_prefix' => '+243',
            'currency' => 'CDF',
            'min_amount' => 2900,
        ],
        'CD' => [
            'name' => 'République Démocratique du Congo',
            'phone_prefix' => '+243',
            'currency' => 'CDF',
            'min_amount' => 2900,
            'api_code' => 'DRC', // Code utilisé pour l'API Shwary
        ],
        'KE' => [
            'name' => 'Kenya',
            'phone_prefix' => '+254',
            'currency' => 'KES',
            'min_amount' => 100,
        ],
        'UG' => [
            'name' => 'Ouganda',
            'phone_prefix' => '+256',
            'currency' => 'UGX',
            'min_amount' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback Configuration
    |--------------------------------------------------------------------------
    |
    | URL where Shwary will send transaction status updates.
    |
    */
    'callback_url' => env('SHWARY_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Timeout Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP timeout settings for API requests (in seconds).
    |
    */
    'timeout' => env('SHWARY_TIMEOUT', 30),

    'connect_timeout' => env('SHWARY_CONNECT_TIMEOUT', 10),
];
