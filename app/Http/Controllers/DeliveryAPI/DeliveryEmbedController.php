<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Blog\BlogService;
use App\Domains\Delivery\DeliveryService;
use Illuminate\Http\Request;

class DeliveryEmbedController
{

    public function handle(Request $request)
    {
        $subdomain = $request->route('subdomain');

        $blog = BlogService::getBlogBySubdomain($subdomain);

        $response = DeliveryService::getLaravelResponse($blog, '');
        $content = $response->content();

        $iframeScript = view('embed.iframe-helpers');
        $iframeScriptAdded = <<<HTML
        <head>
        $iframeScript
        HTML;

        $content = str_replace('<head>', $iframeScriptAdded, $content);

        $response->setContent($content);

        return $response;
    }

}