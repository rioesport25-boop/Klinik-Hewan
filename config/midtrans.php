<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Merchant ID
    |--------------------------------------------------------------------------
    |
    | Your Midtrans Merchant ID obtained from Midtrans Dashboard
    |
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans Client Key for frontend integration
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans Server Key for backend API calls
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Environment
    |--------------------------------------------------------------------------
    |
    | Set to true for Production environment, false for Sandbox/Development
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Sanitization
    |--------------------------------------------------------------------------
    |
    | Enable input sanitization for extra security
    |
    */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /*
    |--------------------------------------------------------------------------
    | Enable 3D Secure
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure for card transactions
    |
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Midtrans URLs
    |--------------------------------------------------------------------------
    |
    | URLs for Midtrans API endpoints
    |
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs for payment notification callbacks
    |
    */
    'notification_url' => env('APP_URL') . '/api/midtrans/notification',
    'finish_url' => env('APP_URL') . '/petshop/payment/finish',
    'unfinish_url' => env('APP_URL') . '/petshop/payment/unfinish',
    'error_url' => env('APP_URL') . '/petshop/payment/error',
];
