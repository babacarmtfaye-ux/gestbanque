<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | Learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',            // Autorise les appels vers ton API
        'v1/*',             // Autorise les appels vers l'API v1
        'oauth/*',          // Nécessaire pour Laravel Passport
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ],

    'allowed_methods' => ['*'], // Autorise toutes les méthodes (GET, POST, PUT, DELETE...)

    'allowed_origins' => ['*'], // Tu peux remplacer * par ton domaine ex: ['http://localhost:4200']

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Autorise tous les headers

    'exposed_headers' => [
        'Authorization',
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],

    'max_age' => 0,

    'supports_credentials' => true, // Important pour les tokens et cookies
];
