<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // internal API routes
    $routes->import('@InternalBundle/src/Comms/Controller', 'attribute');
};