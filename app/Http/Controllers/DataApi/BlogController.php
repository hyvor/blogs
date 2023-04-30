<?php

namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\BlogObject;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController
{
    public function blog(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'language' => 'string',
            'keys' => 'string',
        ]);

        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string) $request->string('language') : null
        );
        $keys = $request->has('keys') ? (string) $request->string('keys') : null;

        return response()->json(
            KeysFilter::filter(new BlogObject($blog, $language), $keys)
        );
    }
}
