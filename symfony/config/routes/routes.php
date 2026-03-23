<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // internal API routes
    $routes->import('@InternalBundle/src/Comms/Controller', 'attribute');

    // sudo API routes
    $routes->import('../../src/Api/Sudo/Controller', 'attribute')
        ->prefix('/api/sudo')
        ->namePrefix('api_sudo_');
};