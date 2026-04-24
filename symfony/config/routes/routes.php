<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // data API
    $routes->import('../../src/Api/Data/Controller', 'attribute')
        ->prefix('/api/data/v0/{subdomain}')
        ->namePrefix('api_data_');

    // console API
    $routes
        ->import('../../src/Api/Console/ControllerOrg', 'attribute')
        ->prefix('/api/console/v0')
        ->namePrefix('api_console_');
    $routes
        ->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console/v0/blog/{subdomain}')
        ->namePrefix('api_console_');

    // delivery API
    $routes
        ->import('../../src/Api/Delivery', 'attribute')
        ->namePrefix('api_delivery_');

    // console API
    $routes->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console/v1/blog/{subdomain}')
        ->namePrefix('api_console_');

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

    // let's encrypt endpoint
    $routes->import('../../src/Api/LetsEncryptController.php', 'attribute');
};
