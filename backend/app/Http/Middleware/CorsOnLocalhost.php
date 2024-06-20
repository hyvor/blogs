<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CorsOnLocalhost
{

    public function handle(Request $request, Closure $next): Response
    {
        if (App::environment('local', 'testing')) {
            $response = $next($request);
            if ($response instanceof Response && method_exists($response, 'header')) {
                $response->header('Access-Control-Allow-Origin', '*');
            }
            return $response;
        }
        return $next($request);
    }