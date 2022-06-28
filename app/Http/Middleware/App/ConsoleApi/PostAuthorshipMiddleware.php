<?php

namespace App\Http\Middleware\App\ConsoleApi;

class PostAuthorshipMiddleware
{
    public function handle($request, $next)
    {
        return $next($request);
    }
}
