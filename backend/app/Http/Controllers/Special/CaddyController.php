<?php

declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Blog\BlogService;
use App\Exceptions\TrustedException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaddyController
{
    public function checkDomain(Request $request): Response
    {
        $request->validate([
            'domain' => 'required|string',
        ]);

        $domain = strval($request->input('domain'));
        $blog = BlogService::getBlogByCustomDomain($domain);

        if (!$blog) {
            return response('Bad domain', 400);
        }

        return response('OK');
    }
}
