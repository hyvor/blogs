<?php
namespace App\Http\Middleware\App\ConsoleAPI;

use App\Exceptions\TrustedException;
use Closure;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\User;
use Illuminate\Http\Request;

class ConsoleApiUserEndpointsAccessMiddleware {

    public function handle(Request $request, Closure $next) {

        $hyvorUser = Login::check();
        if (!$hyvorUser) {
            throw new TrustedException('You are not logged in');
        }

        app()->instance(User::class, $hyvorUser);

        return $next($request);

    }

}