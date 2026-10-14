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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'api_key' => env('API_KEY'),
        'discovery_doc' => 'https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest',
        'redirect' => 'http://127.0.0.1:8000/login/google/callback',
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            // Ajoutez d'autres scopes  ici si nécessaire
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.app.created',
            'https://www.googleapis.com/auth/calendar.calendars',
        ],
    ],
    'deepl' => [
    'url' => 'https://api-free.deepl.com/v2/translate',
    'key' => env('DEEPL_API_KEY'),
],

    // Valeurs lues ici (et non via env() dans le code) pour que `php artisan config:cache` fonctionne.
    'recaptcha' => [
        'secret' => env('GOOGLE_RECAPTCHA_SITE_SECRET'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'from_address' => env('MAIL_FROM_ADDRESS'),
        'from_name' => env('MAIL_FROM_NAME'),
    ],

    'ml' => [
        'url' => env('ML_API_URL', 'http://localhost:5000'),
    ],

    // null si FRONTEND_URL n'est pas défini : chaque appelant garde son propre repli historique.
    'frontend_url' => env('FRONTEND_URL'),

];
