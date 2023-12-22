<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */


return [

    // domains
    'domain_app' => env('DOMAIN_APP', '127.0.0.1'),
    'domain_delivery' => env('DOMAIN_DELIVERY', 'hyvorblogs.io'),
    'domain_hyvor' => env('DOMAIN_HYVOR', 'hyvor.com'),

    'logo' => '/img/logo.png',

    // monthly
    'pricing' => [
        'starter' => 9,
        'growth' => 19,
        'premium' => 49,
        'team' => 299,
        'business' => 699,
        'enterprise' => 1299
    ],

];
