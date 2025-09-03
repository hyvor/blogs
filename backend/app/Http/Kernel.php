<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Middleware\HandleCors;
use Hyvor\Internal\Metric\MetricMiddleware;

class Kernel extends HttpKernel
{

    protected $middleware = [
        \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrimStrings::class,
        MetricMiddleware::class,
    ];


    protected $middlewareGroups = [
        'web' => [],
    ];


    protected $routeMiddleware = [
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'role' => Middleware\App\ConsoleApi\RoleMiddleware::class,
    ];
}
