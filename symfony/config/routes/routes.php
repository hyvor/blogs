<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * FrankenPHP sets router env:
 * - app: for app routes (ex: blogs.hyvor.com)
 * - subdomain: for subdomain delivery (ex: subdomain.hyvorblogs.io)
 * - customdomain: for custom domain delivery
 * - local: for local endpoints (ex: caddy certificate)
 */
return static function (RoutingConfigurator $routes): void {
    // app routes
    // (ex: blogs.hyvor.com)
    $routes
        ->import('./app.php')
        ->condition('service("app_router").isApp()');

    // subdomain delivery (ex: subdomain.hyvorblogs.io)
    $routes
        ->import('../../src/Api/Delivery/SubdomainController.php', 'attribute')
        ->condition('service("app_router").isSubdomain()');

    // custom domain delivery
    $routes
        ->import('../../src/Api/Delivery/CustomDomainController.php', 'attribute')
        ->condition('service("app_router").isCustomDomain()');

    // local endpoints
    $routes
        ->import('../../src/Api/Local/LocalController.php', 'attribute')
        ->condition('service("app_router").isLocal()');
};
