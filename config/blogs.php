<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */
return [

    // domains
    'domain_app' => env('DOMAIN_APP'),
    'domain_delivery' => env('DOMAIN_DELIVERY'),

    'logo' => '/img/logo.png',

    /**
     * Paddle plans
     * 
     * id is the paddle plan ID
     */
    'paddle_plans' => [
        [
            'id' => env('APP_ENV') !== 'production' ? 21525 : 0,
            'name' => 'pro',
            'frequency' => 'yearly',
            'price' => 20
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21526 : 0,
            'name' => 'team',
            'frequency' => 'monthly',
            'price' => 8
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21527 : 0,
            'name' => 'team',
            'frequency' => 'yearly',
            'price' => 60
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21528 : 0,
            'name' => 'enterprise',
            'frequency' => 'monthly',
            'price' => 800
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21529 : 0,
            'name' => 'enterprise',
            'frequency' => 'yearly',
            'price' => 6000
        ]
    ]

];