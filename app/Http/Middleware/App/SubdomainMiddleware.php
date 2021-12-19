<?php

namespace App\Http\Middleware\App;

use App\Models\Blog;
use Closure;

class SubdomainMiddleware {

    public function handle($request, Closure $next) {
       
        $subdomain = $request->route('subdomain');

        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            
        }

    }

}
