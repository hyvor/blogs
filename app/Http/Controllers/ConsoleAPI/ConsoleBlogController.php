<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Blog\BlogCountsRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class ConsoleBlogController extends Controller {

    public function getPostsCounts(Blog $blog) {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }
}