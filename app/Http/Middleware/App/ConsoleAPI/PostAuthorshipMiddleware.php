<?php

namespace App\Http\Middleware\App\ConsoleAPI;

class PostAuthorshipMiddleware
{
    public function handle($request, $next)
    {
        return $next($request);
    }
}
