<?php

return [
    'component' => 'blogs',
    'auth' => [
        'routes' => true,
        'routes_domain' => env('DOMAIN_APP', 'blogs.hyvor.com'),
    ]
];
