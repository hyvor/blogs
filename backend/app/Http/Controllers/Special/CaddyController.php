<?php declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Blog\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaddyController
{
    public function checkDomain(Request $request) : Response
    {
        $request->validate([
            'domain' => 'required|string',
        ]);

        $domain = strval($request->input('domain'));
        $blog = BlogService::getBlogByCustomDomain($domain);

        if (!$blog) {
            $hasWww = str_contains($domain, 'www.');
            $normalizedHost = str_replace('www.', '', $domain);
            $alternativeHost = $hasWww ? $normalizedHost : 'www.' . $normalizedHost;
            $blog = BlogService::getBlogByCustomDomain($alternativeHost);
            if (!$blog)
                abort(500);
            else
                return response('OK');
        }

        return response('OK');
    }
}
