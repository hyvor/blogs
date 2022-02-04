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

    public function getCustomCode(Blog $blog) {

        $getCustomCode = BlogRepository::getCustomCode($blog->id);
        return response()->json($getCustomCode);
    }

    public function updateBlog(Request $request, Blog $blog) {

        // $id = $request->route('id');
        // $codeHead = $request->input('code_head');
        // $codeFooter = $request->input('code_footer');

        $codeHead = 'eloquent testing head';
        $codeFooter = 'footer eloquent';

        $updateCustomCode = BlogRepository::updateBlog($blog->id, $codeHead, $codeFooter);
        return response()->json($updateCustomCode);
    }
}
