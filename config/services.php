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

    /*
    | AI listing assistant (Groq). Leave GROQ_API_KEY empty to hide the feature.
    */
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'fallback_model' => env('GROQ_FALLBACK_MODEL', 'llama-3.3-70b-versatile'),
        'temperature' => (float) env('GROQ_TEMPERATURE', 0.2),
        'timeout' => (int) env('GROQ_TIMEOUT', 60),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY', 'pk_test_dummy'),
        'secret' => env('STRIPE_SECRET', 'sk_test_dummy'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', 'whsec_dummy'),
    ],

];
