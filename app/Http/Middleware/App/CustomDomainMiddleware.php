<?php
namespace App\Http\Middleware\App;

use App\Domains\Blog\BlogRepository;
use App\Models\Blog;
use Closure;

class CustomDomainMiddleware 
{

    public function handle($request, Closure $next) 
    {
        $host = $request->getHost();

        $blog = BlogRepository::getBlogByCustomDomain($host);

        if (!$blog) {
            abort(404);
        }

        app()->instance(Blog::class, $blog);

        return $next($request);
    }

}
