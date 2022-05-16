<?php

namespace App\Http\Middleware\App\CliAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\BlogRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Http\Request;

class CliAPIMiddleware
{
    public function handle(Request $request, $next)
    {
        $subdomain = $request->route('subdomain');

        if (! $subdomain) {
            throw new TrustedException('Subdomain should be set');
        }

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        /*if ($blog === null || $blog->type !== BlogTypeEnum::DEV) {
            throw new TrustedException('Invalid Subdomain');
        }*/

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
