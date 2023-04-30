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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'iframely' => [
        'key' => env('IFRAMELY_KEY'),
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
        'secret_key' => env('UNSPLASH_SECRET_KEY'),
    ],

    'paddle' => [
        'vendor_id' => env('PADDLE_VENDOR_ID'),
        'vendor_auth_code' => env('PADDLE_VENDOR_AUTH_CODE'),
        'public_key' => env('PADDLE_PUBLIC_KEY'),
        'sandbox' => env('PADDLE_SANDBOX', false),
    ],

    'shopify' => [
        'api_key' => env('SHOPIFY_API_KEY'),
        'api_secret_key' => env('SHOPIFY_API_SECRET_KEY')
    ],

    'github' => [
        'themes_publish_key' => env('GITHUB_THEMES_PUBLISH_KEY'),
    ],

    'email_octopus' => [
        'api_key' => env('EMAIL_OCTOPUS_API_KEY'),
        'list_id' => "7b95c9e8-9e4f-11ed-b3ed-47583ae33f24"
    ],

    'deepl' => [
        'api_key' => env('DEEPL_API_KEY'),

    ]

];
