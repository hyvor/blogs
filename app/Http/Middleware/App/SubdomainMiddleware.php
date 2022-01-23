<?php

namespace App\Http\Middleware\App;

use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;

class SubdomainMiddleware
{
    public function handle($request, Closure $next)
    {

        $subdomain = $request->route('subdomain');

        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            throw new TrustedException('Blog not found - invalid subdmoain', 400);
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
