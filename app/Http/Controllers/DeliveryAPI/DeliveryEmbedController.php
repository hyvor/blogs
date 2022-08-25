<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Blog\BlogService;
use App\Domains\Delivery\DeliveryService;
use App\Domains\Delivery\Embed\EmbedHtmlProcessor;
use App\Exceptions\TrustedException;
use Illuminate\Http\Request;

class DeliveryEmbedController
{
    public function embedJs(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string',
            'path_style' => 'bool',
            'path' => 'string|nullable'
        ]);

        $subdomain = $request->input('subdomain');
        $pathStyle = $request->boolean('path_style');
        $path = $request->input('path');

        $js = view('embed.embed-js', [
            'subdomain' => $subdomain,
            'pathStyle' => $pathStyle,
            'path' => $path
        ]);

        return response($js)->header('Content-Type', 'application/javascript');
    }

    public function iframe(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'path_style' => 'bool'
        ]);

        $subdomain = $request->route('subdomain');
        $embeddingUrl = trim($request->input('url'), '/');
        $pathStyle = $request->boolean('path_style');
        $path = $request->input('path') ?? '';

        $blog = BlogService::getBlogBySubdomain($subdomain);

        if (!$blog->getMeta('embeddable')) {
            throw new TrustedException('Not embeddable');
        }

        $response = DeliveryService::getLaravelResponse($blog, $path);
        $content = $response->content();

        $content = (new EmbedHtmlProcessor($blog, $embeddingUrl, $content, $pathStyle))->get();

        $response->setContent($content);

        return $response;
    }
}
