<?php

namespace App\Http\Middleware\App\ConsoleAPI;

use App\Exceptions\TrustedException;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\Login;
use Illuminate\Http\Request;

class ConsoleMiscApiAccessMiddleware
{
    public function handle(Request $request, $next)
    {
        $hyvorUser = Login::check();
        if (! $hyvorUser) {
            throw new TrustedException('You are not logged in');
        }

        app()->instance(HyvorUser::class, $hyvorUser);

        return $next($request);
    }
}
