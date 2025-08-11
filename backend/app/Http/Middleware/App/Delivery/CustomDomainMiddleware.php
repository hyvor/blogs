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

        if (!$request->isSecure()) {
            $fullUrl = 'https://' . $host . $request->getRequestUri();
            return redirect()->to($fullUrl);
        }

        $blog = BlogService::getBlogByCustomDomain($host);

        if (!$blog) {
            return redirect('https://blogs.hyvor.com');
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }
}
