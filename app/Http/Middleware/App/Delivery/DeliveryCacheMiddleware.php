<?php

namespace App\Http\Middleware\App\Delivery;

class DeliveryCacheMiddleware
{
    public function handle($request, $next)
    {
        $path = $request->route('path') ?? '';
        $query = $request->all();

        return $next($request);
    }

    public static function checkCache()
    {
    }
}
