<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Domains\Blog\BlogCountsRepository;
use App\Domains\Blog\BlogRepository;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleBlogController extends Controller
{
    public function getBlog(Blog $blog)
    {
        return response()->json(new BlogObject($blog));
    }

    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog));
    }

    /*
    *
    * ConsoleAPI Settings->General
    *
    */

    public static function updateBlog(Request $request, Blog $blog)
    {
        $updates = $request->all();
        $blog = BlogRepository::updateBlog($blog, $updates);

        return response()->json(new BlogObject($blog));
    }

    public static function createBlogVariant(Request $request, Blog $blog)
    {
        $languageId = $request->input('languageId');
        $createVariant = BlogRepository::createBlogVariant($blog, $languageId);

        return response()->json($createVariant);
    }

    public static function updateBlogFeatureImage(Request $request, Blog $blog)
    {
        $file = $request->file('featureImage');
        // $request->validate([
        //     'file' => 'required|file'
        // ]);

        $featureImage = BlogRepository::updateBlogFeatureImage($blog, $file);

        return response()->json($featureImage);
    }

    public static function updateBlogIcon(Request $request, Blog $blog)
    {
        $file = $request->file('icon');
        // $request->validate([
        //     'file' => 'required|file'
        // ]);

        $icon = BlogRepository::updateBlogIcon($blog, $file);

        return response()->json($icon);
    }
}
