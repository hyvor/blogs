<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Domains\Blog\BlogCountsRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;

use Illuminate\Http\Request;
use App\Domains\Blog\BlogRepository;
use App\Data\Objects\ConsoleAPI\Blog\BlogGeneralObject;


class ConsoleBlogController extends Controller
{

    public function getBlog(Blog $blog) {
        return response()->json(new BlogObject($blog));
    }

    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }

    /*
    *
    * ConsoleAPI Settings->General
    *
    */
    public static function getBlogGeneral(Blog $blog)
    {
        $getData = BlogRepository::getBlogGeneral($blog)
                ->map(function ($tags) use ($blog) {
                    return new BlogGeneralObject($tags, $blog);
            });
        return response()->json($getData);
    }

    public static function updateBlogGeneral(Request $request, Blog $blog)
    {
        $languageId =  $request->input('languageId');
        
        if($request->has('subdomain')) {
            $blogData['subdomain'] = $request->input('subdomain');
        }

        // Should be changed
        if ($request->has('icon')) {
            $blogData['icon'] = $request->input('icon');
        }
        if ($request->has('featureImageId')) {
            $blogData['featureImageId'] = $request->input('featureImageId');
        }

        if ($request->has('social_facebook')) {
            $blogData['social_facebook'] = $request->input('social_facebook');
        }
        if ($request->has('social_twitter')) {
            $blogData['social_twitter'] = $request->input('social_twitter');
        }

        if ($request->has('social_linkedin')) {
            $blogData['social_linkedin'] = $request->input('social_linkedin');
        }

        if ($request->has('social_youtube')) {
            $blogData['social_youtube'] = $request->input('social_youtube');
        }

        if ($request->has('social_instagram')) {
            $blogData['social_instagram'] = $request->input('social_instagram');
        }

        // Variants
        if ($request->has('name')) {
            $blogData['name'] = $request->input('name');
        }

        if ($request->has('description')) {
            $blogData['description'] = $request->input('description');
        }

        
        $updateBlogData = BlogRepository::updateBlogGeneral($blog, $languageId, $blogData);
        return response()->json($updateBlogData);
    }

    public static function createBlogGeneralVariant(Request $request, Blog $blog)
    {
        $languageId = $request->input('languageId');
        $createVariant = BlogRepository::createBlogGeneralVariant($blog, $languageId);

        return response()->json($createVariant);
    }



}
