<?php

namespace App\Http\Middleware\App;

use Closure;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Hyvor\HyvorConnecter\User;
use Illuminate\Http\Request;

class LoginRequiredElseRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Login::check();
        if (! $user) {
            return Redirect::to('login');
        }

        app()->instance(User::class, $user);

        return $next($request);
    }
}
