<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Domains\Blog\BlogCountsRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class ConsoleBlogController extends Controller
{

    public function getBlog(Blog $blog) {
        return response()->json(new BlogObject($blog));
    }

    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }
}
