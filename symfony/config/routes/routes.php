<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // console API
    $routes
        ->import('../../src/Api/Console/ControllerOrg', 'attribute')
        ->prefix('/api/console/v0')
        ->namePrefix('api_console_');
    $routes
        ->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console/v0/blog/{subdomain}')
        ->namePrefix('api_console_');

    // blog delivery routes
    $routes
        ->import('../../src/Api/BlogDelivery', 'attribute')
        ->namePrefix('blog_delivery_');

    // internal API routes
    $routes->import('@InternalBundle/src/Comms/Controller', 'attribute');

    // OIDC routes
    $routes->import('@InternalBundle/src/Controller/OidcController.php', 'attribute')
        ->prefix('/api/oidc')
        ->namePrefix('api_oidc_');

    // sudo API routes
    $routes->import('../../src/Api/Sudo/Controller', 'attribute')
        ->prefix('/api/sudo')
        ->namePrefix('api_sudo_');
};
