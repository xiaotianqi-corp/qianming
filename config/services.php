<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'uanataca' => [
        'base_url' => env('UANATACA_BASE_URL', 'https://api.uanataca.com/v1'),
        'webhook_url' => env('UANATACA_WEBHOOK_URL', 'https://api.uanataca.com/webhook'),
        'api_key'  => env('UANATACA_API_KEY'),
        'webhook_secret' => env('UANATACA_WEBHOOK_SECRET'),
        'allowed_ips' => explode(',', env('UANATACA_ALLOWED_IPS', '127.0.0.1')),
    ],
    
    'payments' => [
        'default' => env('PAYMENT_NAME', 'default'),
    ],
];
