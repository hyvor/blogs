<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * X-Router = delivery is set by Caddyfile for non-app routes (blog delivery)
 */
return static function (RoutingConfigurator $routes): void {
    // app routes
    // (ex: blogs.hyvor.com)
    $routes
        ->import('./app.php')
        ->condition('request.headers.get("X-Router") === null');

    // delivery routes
    // (ex: subdomain.hyvorblogs.io and custom domains)
    $routes
        ->import('./delivery.php')
        ->condition('request.headers.get("X-Router") === "delivery"');
};
