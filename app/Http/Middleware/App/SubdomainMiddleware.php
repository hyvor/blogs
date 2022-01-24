<?php

namespace App\Http\Middleware\App;

use App\Exceptions\SubdomainNotFoundException;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;

class SubdomainMiddleware
{
    public function handle($request, Closure $next)
    {

        $subdomain = $request->route('subdomain');

        if (!$subdomain) {
            throw new TrustedException('Subdomain is required', TrustedException::ERROR_BAD_REQUEST);
        }

        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            throw new SubdomainNotFoundException(
                'Blog not found - invalid subdmoain',
                TrustedException::ERROR_BAD_REQUEST
            );
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
