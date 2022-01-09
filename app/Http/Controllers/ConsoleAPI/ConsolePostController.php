<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
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

    public function createPost(Request $request, Blog $blog) {
        $isPage = (bool) $request->input('is_page');
        $post = PostRepository::createPost($blog->id, $isPage);
        return response()->json(new PostOutputType($post, $blog, true));
    }

    public function getPost(Request $request, Blog $blog) {
        $postId = (int) $request->route('id');
        $post = PostRepository::postById($postId);
        if ($post->blog_id !== $blog->id) {
            throw new TrustedException('Post does not belong to this blog', 401);
        }
        return response()->json(new PostOutputType($post, $blog, true));
    }

    public function deletePost(Request $request, Blog $blog) {
        $postId = $request->route('id');
        $post = PostRepository::postById($postId);
        if ($post->blog_id !== $blog->id) {
            throw new TrustedException('Post does not belong to this blog', 401);
        }
        PostRepository::deletePost($postId, $blog->id);
    }

    public function updatePost(Request $request, Blog $blog) {
        $postId = $request->route('id');
        $post = PostRepository::postById($postId);
        
        if ($post->blog_id !== $blog->id) {
            throw new TrustedException('Post does not belong to this blog', 401);
        }
        $updates = [];

        if ($request->has('content')) $updates['content'] = $request->input('content');
        if ($request->has('title')) $updates['title'] = $request->input('title');

        $post = PostRepository::updatePost($postId, $updates);
        return response()->json(new PostOutputType($post, $blog, true));
    }

}