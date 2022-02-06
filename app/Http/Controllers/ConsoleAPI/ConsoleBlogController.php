<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Blog\BlogCountsRepository;
use App\Domains\Blog\BlogRepository;

use App\Models\Blog;

class ConsoleBlogController extends Controller
{
    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }

    public function getBlogData(Blog $blog) {
        $getBlogData = BlogRepository::getBlogData($blog->id);
        return response()->json($getBlogData);
    }

    public function updateBlog(Request $request, Blog $blog) {

        // $id = $request->route('id');
        $codeHead = $request->input('codeHead');
        $codeFooter = $request->input('codeFooter');

        // $codeHead = 'eloquent testing head';
        // $codeFooter = 'footer eloquent';

        $updateCustomCode = BlogRepository::updateBlog($blog->id, $codeHead, $codeFooter);
        return response()->json($updateCustomCode);
    }
}
