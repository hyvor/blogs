<?php

namespace App\Http\Middleware\App\CliAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\BlogService;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class CliAPIMiddleware
{

    public function handle(Request $request, Closure $next) : mixed
    {
        $subdomain = strval($request->route('subdomain'));

        if (!$subdomain) {
            throw new TrustedException('The subdomain should be set');
        }

        $blog = BlogService::getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new TrustedException('Invalid subdomain');
        }

        if ($blog->type !== BlogTypeEnum::DEV) {
            throw new TrustedException('Please use a DEV blog');
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
