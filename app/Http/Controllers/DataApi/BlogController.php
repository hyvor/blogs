<?php

namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\BlogObject;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController
{

    public function blog(Request $request, Blog $blog)
    {

        $request->validate([
            'language' => 'string',
            'keys' => 'string'
        ]);

        $language = Helper::getLanguage($blog, $request->input('language'));
        $keys = $request->input('keys');

        return response()->json(
            KeysFilter::filter(new BlogObject($blog, $language), $keys)
        );

    }

}