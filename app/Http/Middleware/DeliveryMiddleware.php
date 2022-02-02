<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Blog;

class DeliveryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $blogExistence = Blog::select('deleted_at')
            // ->value('deleted_at');
            ->get();
        // dd($blogExistence);
        $subdomainExistence = Blog::select('subdomain')
            // ->value('deleted_at');
            ->get();

        foreach ($subdomainExistence as $subdomain) {
            if ($subdomain['subdomain'] != null) {
                foreach ($blogExistence as $blog) {
                    if ($blog['deleted_at'] != null) {
                        echo "hello 280000";
                        // return redirect(' ');
                    } else {
                        return $next($request);
                    }
                }
            } else {
                // return redirect(' ');
                echo "hello 88888";
            }
        }
    }
}
