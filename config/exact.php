<?php

declare(strict_types=1);

return [
    'api'  => [
        'base_uri' => env('EXACT_API_BASE_URI', 'https://start.exactonline.nl/api/'),
        'version'  => env('EXACT_API_VERSION', 'v1/2878128/'),
    ],
    'auth' => [
        'client_id'     => env('EXACT_CLIENT_ID'),
        'client_secret' => env('EXACT_CLIENT_SECRET'),
        'redirect_uri'  => env('EXACT_REDIRECT_URI'),
    ],
];
