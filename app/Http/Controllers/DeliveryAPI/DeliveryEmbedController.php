<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Blog\BlogService;
use App\Domains\Delivery\DeliveryService;
use App\Domains\Delivery\Embed\HtmlProcessor;
use App\Exceptions\TrustedException;
use Illuminate\Http\Request;

class DeliveryEmbedController
{

    public function embedJs(Request $request)
    {

        $request->validate([
            'subdomain' => 'required|string'
        ]);

        $subdomain = $request->input('subdomain');

        $js = view('embed.embed-js', [
            'domain' => 'https://blogs.hyvor.com',
            'subdomain' => $subdomain
        ]);

        return response($js)->header('Content-Type', 'application/javascript');

    }

    public function iframe(Request $request)
    {

        $request->validate([
            'url' => 'required|url',
        ]);

        $subdomain = $request->route('subdomain');
        $embeddingUrl = $request->input('url');
        $path = $request->input('path') ?? '';

        $blog = BlogService::getBlogBySubdomain($subdomain);

        if (!$blog->getMeta('embeddable')) {
            throw new TrustedException('Not embeddable');
        }

        $response = DeliveryService::getLaravelResponse($blog, $path);
        $content = $response->content();

        $content = (new HtmlProcessor($blog, $embeddingUrl, $content))->get();

        $response->setContent($content);

        return $response;
    }

}