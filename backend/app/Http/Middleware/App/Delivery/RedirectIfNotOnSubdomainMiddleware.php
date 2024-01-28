<?php declare(strict_types=1);

namespace App\Http\Middleware\App\Delivery;

use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class RedirectIfNotOnSubdomainMiddleware
{

    public function handle(Request $request, Closure $next) : mixed
    {

        $blog = app(Blog::class);

        if ($blog->hosting_at !== BlogHostingAtEnum::SUBDOMAIN) {
            $path = $request->path();
            $path = ltrim($path, '/');
            return redirect()->to(
                PermalinkRepository::getBaseUrl($blog) .
                ($path ? '/' . $path : '')
            );
        }

        return $next($request);

    }

}