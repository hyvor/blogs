<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Blog\BlogCountsRepository;
use App\Models\Blog;

class ConsoleBlogController {

    public function getPostsCounts(Blog $blog) {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }
}