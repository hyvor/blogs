<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Misc;

use App\Domains\Post\Content\PostContentService;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleMiscProsemirrorController
{

    public function getJson(Blog $blog, Request $request) : JsonResponse
    {
        $request->validate([
            'html' => 'string|required'
        ]);

        $html = (string) $request->string('html');
        $json = PostContentService::getJsonFromHtml($html, $blog);

        return response()->json([
            'json' => $json
        ]);
    }

}