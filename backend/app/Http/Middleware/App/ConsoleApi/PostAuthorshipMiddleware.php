<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use Closure;
use Illuminate\Http\Request;

class PostAuthorshipMiddleware
{
    public function handle(Request $request, Closure $next) : mixed
    {
        return $next($request);
    }
}
