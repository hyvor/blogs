<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */


return [

    // domains
    'domain_app' => env('DOMAIN_APP', 'localhost'),

    // @deprecated Use delivery_url
    'delivery_domain' => env('DELIVERY_DOMAIN', 'localhost'),
    'delivery_url' => env('DELIVERY_URL', 'https://localhost:2211'),

    'domain_hyvor' => env('DOMAIN_HYVOR', 'hyvor.com'),

    'logo' => '/img/logo.png',

];
