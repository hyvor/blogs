<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Caddyfile sets X-Router header:
 * - subdomain: for subdomain delivery (ex: subdomain.hyvorblogs.io)
 * - customdomain: for custom domain delivery
 */
return static function (RoutingConfigurator $routes): void {
    // app routes
    // (ex: blogs.hyvor.com)
    $routes
        ->import('./app.php')
        ->condition('request.headers.get("X-Router") === null');

    // subdomain delivery (ex: subdomain.hyvorblogs.io)
    $routes
        ->import('../../src/Api/Delivery/SubdomainController.php', 'attribute')
        ->condition('request.headers.get("X-Router") === "subdomain"');

    // custom domain delivery
    $routes
        ->import('../../src/Api/Delivery/SubdomainController.php', 'attribute')
        ->condition('request.headers.get("X-Router") === "subdomain"');
};
