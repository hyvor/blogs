<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // console API
    $routes
        ->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/v2/console')
        ->namePrefix('api_console_');

    // internal API routes
    $routes->import('@InternalBundle/src/Comms/Controller', 'attribute');

    // sudo API routes
    $routes->import('../../src/Api/Sudo/Controller', 'attribute')
        ->prefix('/api/sudo')
        ->namePrefix('api_sudo_');
};
