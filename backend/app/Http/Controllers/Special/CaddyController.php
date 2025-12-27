<?php

declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Blog\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CaddyController
{
    public function checkDomain(Request $request): Response
    {
        Log::info("Info from CaddyController/checkDomain", [
            "domain" => $request->input('domain'),
            "host" => $request->getHost(),
            "url" => $request->getBaseUrl()
        ]);

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
