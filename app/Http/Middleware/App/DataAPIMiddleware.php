<?php

namespace App\Http\Middleware\App;

use App\Exceptions\DataAPIException;
use App\Models\Blog;
use App\Domains\Blog\BlogRepositoryInterface;
use Closure;
use Exception;

class DataAPIMiddleware
{
    private $blogRepo;

    public function __construct(BlogRepositoryInterface $blogRepo)
    {
        $this->blogRepo = $blogRepo;
    }

    public function handle($request, Closure $next)
    {

        $subdomain = $request->input('subdomain');

        if (is_null($subdomain)) {
            throw new DataAPIException('Subdomain param is required');
        }

        $blog = $this->blogRepo->bySubdomain($subdomain);

        if (!$blog) {
            throw new DataAPIException("Subdomain not found ($subdomain)", 400);
        }

        /**
         *
         * Todo: Change this to use Dependency Injection like SubdomainMiddleware.php
         */
        $request->attributes->set('blog', $blog);

        return $next($request);
    }
}
