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

    /*
    |--------------------------------------------------------------------------
    | Payment gateways
    |--------------------------------------------------------------------------
    | Credentials must be read through config (never env()) so `config:cache`
    | in production does not silently blank them out.
    */

    'razorpay' => [
        'key' => env('RAZORPAY_KEY', 'rzp_test_dummy'),
        'secret' => env('RAZORPAY_SECRET', 'secret_dummy'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', 'whsec_dummy'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY', 'pk_test_dummy'),
        'secret' => env('STRIPE_SECRET', 'sk_test_dummy'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', 'whsec_dummy'),
    ],

];
