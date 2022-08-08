<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Blog\BlogService;
use App\Domains\Delivery\DeliveryService;
use App\Domains\Delivery\Embed\HtmlProcessor;
use Illuminate\Http\Request;

class DeliveryEmbedController
{

    public function embedJs(Request $request)
    {

        $request->validate([
            'subdomain' => 'required|string'
        ]);

        $subdomain = $request->input('subdomain');

        return view('embed.embed', [
            'domain' => 'https://blogs.hyvor.com',
            'subdomain' => $subdomain
        ]);

    }

    public function iframe(Request $request)
    {
        $subdomain = $request->route('subdomain');
        $embeddingUrl = $request->input('url');
        $path = $request->input('path') ?? '';

        $blog = BlogService::getBlogBySubdomain($subdomain);

        $response = DeliveryService::getLaravelResponse($blog, $path);
        $content = $response->content();

        $content = (new HtmlProcessor($blog, $embeddingUrl, $content))->get();

        $response->setContent($content);

        return $response;
    }

}