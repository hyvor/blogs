<?php

namespace App\Http\Middleware\App\ConsoleApi;

use App\Exceptions\TrustedException;
use Closure;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\Login;
use Illuminate\Http\Request;

class ConsoleApiUserEndpointsAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $hyvorUser = Login::check();
        if (! $hyvorUser) {
            throw new TrustedException('You are not logged in');
        }

        app()->instance(HyvorUser::class, $hyvorUser);

        return $next($request);
    }
}
