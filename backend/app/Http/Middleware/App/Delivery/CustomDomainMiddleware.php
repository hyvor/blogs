<?php

namespace App\Http\Middleware\App\Delivery;

use App\Domains\Blog\BlogService;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class CustomDomainMiddleware
{
    public function handle(Request $request, Closure $next) : mixed
    {
        $host = $request->getHost();
        $blog = BlogService::getBlogByCustomDomain($host);

        if (!$blog) {
            $hasWww = str_contains($host, 'www.');
            $normalizedHost = str_replace('www.', '', $host);
            $alternativeHost = $hasWww ? $normalizedHost : 'www.' . $normalizedHost;

            $blog = BlogService::getBlogByCustomDomain($alternativeHost);
            if ($blog) {
                return redirect()->to('https://' . $alternativeHost . $request->getRequestUri());
            }
            return redirect('https://blogs.hyvor.com');
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
