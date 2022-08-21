<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */


return [

    // domains
    'domain_app' => env('DOMAIN_APP', 'blogs.hyvor.com'),
    'domain_delivery' => env('DOMAIN_DELIVERY', 'hyvorblogs.io'),
    'domain_hyvor' => env('DOMAIN_HYVOR', 'hyvor.com'),

    'logo' => '/img/logo.png',

    // monthly
    'pricing' => [
        'A' => 19,
        'B' => 49,
        'C' => 299,
        'D' => 699,
        'E' => 1299
    ],

];
