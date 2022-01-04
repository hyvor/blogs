<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Repositories\Post\PostRepository;
use App\Types\Post\PostsFiltersType;
use Illuminate\Http\Request;

class ConsolePostController {

    public function getPosts(Request $request, Blog $blog) {

        $posts = PostRepository::getPosts(
            $blog,
            (new PostsFiltersType())
                ->setStatus($request->input('status'))
                ->setAuthorId($request->input('author_id'))
                ->setTagId($request->input('tag_id'))
                ->setStartTimestamp($request->input('start_timestamp'))
                ->setEndTimestamp($request->input('end_timestamp'))
                ->setSearch($request->input('search')),
            $request->input('limit') ?? 0,
            $request->input('offset') ?? 0
        );

        return response()->json($posts);
    }

}