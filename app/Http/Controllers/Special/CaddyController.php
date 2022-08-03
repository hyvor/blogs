<?php

namespace App\Http\Controllers\Special;

use App\Domains\Blog\BlogService;
use Illuminate\Http\Request;

class CaddyController
{
    public function checkDomain(Request $request)
    {
        $request->validate([
            'domain' => 'required|string'
        ]);

        $domain = $request->input('domain');
        $blog = BlogService::getBlogByCustomDomain($domain);

        if (! $blog) {
            abort(500);
        }

        return response('OK');
    }
}
