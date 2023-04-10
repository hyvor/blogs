<?php

namespace App\Http\Middleware\App;

use Closure;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Illuminate\Http\Request;

class LoginRequiredElseRedirectMiddleware
{
    public function handle(Request $request, Closure $next) : mixed
    {
        $user = Login::check();
        if (! $user) {
            return Redirect::toLogin();
        }

        app()->instance(HyvorUser::class, $user);

        return $next($request);
    }
}
