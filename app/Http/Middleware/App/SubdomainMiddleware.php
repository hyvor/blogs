<?php

namespace App\Http\Middleware\App;

use App\Domains\Blog\BlogRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;

class SubdomainMiddleware
{
    public function handle($request, Closure $next)
    {

        $subdomain = $request->route('subdomain');

        if (!$subdomain) {
            throw new TrustedException('Subdomain not found', TrustedException::ERROR_NOT_FOUND);
        }

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new TrustedException('Subdomain not found', TrustedException::ERROR_NOT_FOUND);
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
