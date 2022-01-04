<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Domains\Post\PostRepository;
use App\Types\Post\PostInputListFiltersType;
use App\Types\Post\PostOutputType;
use Illuminate\Http\Request;

class ConsolePostController {

    public function getPostsCounts(Request $request, Blog $blog) {
        return response()->json(
          //  PostRepository::getPostsCounts($blog->id, 0)
        );
    }

    public function getPosts(Request $request, Blog $blog) {

        $filters = json_decode($request->input('filters'));
        $posts = PostRepository::getPosts(
            $blog->id,
            (new PostInputListFiltersType())
                ->setStatus($filters->status)
                ->setAuthorId($filters->author === 'all' ? null : $filters->author)
                ->setTagId($filters->tag === 'all' ? null : $filters->tag)
                ->setStartTimestamp($filters->dateStart)
                ->setEndTimestamp($filters->dateEnd)
                ->setSearch($filters->search),
            $request->input('limit'),
            $request->input('offset') ?? 0
        )->map(function($post) use ($blog) {
            return new PostOutputType($post, $blog, true);
        });

        return response()->json($posts);
    }

}