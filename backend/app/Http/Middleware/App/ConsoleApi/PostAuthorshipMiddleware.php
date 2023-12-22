<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

class PostAuthorshipMiddleware
{
    public function handle($request, $next) : mixed
    {
        return $next($request);
    }
}
