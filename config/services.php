<?php

declare(strict_types=1);
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
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses'    => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // TODO Rename this configuration key in accordance to the name of the platform being integrated
    // TODO For example, if the platform is eBay then the key below should be renamed from 'remote' to 'ebay'
    'remote' => [
        'oauth2' => [
            'client_id'     => env('REMOTE_OAUTH2_CLIENT_ID'),
            'client_secret' => env('REMOTE_OAUTH2_CLIENT_SECRET'),
            'redirect_uri'  => env('REMOTE_OAUTH2_REDIRECT_URI'),
        ],
    ],
];
