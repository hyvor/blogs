<?php

namespace App\Http\Middleware\App;

use App\Domains\Blog\BlogService;
use App\Exceptions\SubdomainNotFoundException;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class SubdomainMiddleware
{

    public function handle(Request $request, Closure $next) : mixed
    {
        $subdomain = strval($request->route('subdomain'));

        if (!$subdomain) {
            throw new TrustedException('Subdomain missing', TrustedException::ERROR_NOT_FOUND);
        }

        $blog = BlogService::getBlogBySubdomain($subdomain);

        if (! $blog) {
            throw new SubdomainNotFoundException('Subdomain not found', TrustedException::ERROR_NOT_FOUND);
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
