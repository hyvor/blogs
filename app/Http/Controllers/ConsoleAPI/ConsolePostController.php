<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Types\Post\PostInputListFiltersType;
use App\Types\Post\PostOutputType;
use Illuminate\Http\Request;

class ConsolePostController extends Controller {

    public function getPosts(Request $request, Blog $blog) {
        $filters = json_decode($request->input('filters'));
        $posts = PostRepository::getPosts(
            $blog->id,
            (new PostInputListFiltersType())
                ->setStatus($filters->status === 'all' ? null : $filters->status)
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

        /**
         * Some strings become null when empty
         * So, always use ->has() to check if the variable is set
         */
        if ($request->has('published_at')) 
            $updates['published_at'] = $request->input('published_at');

        if ($request->has('status')) 
            $updates['status'] = $request->input('status');

        if ($request->has('is_featured')) 
            $updates['is_featured'] = (bool) $request->input('is_featured');

        if ($request->has('slug')) 
            $updates['slug'] = $request->input('slug');

        if ($request->has('content')) 
            $updates['content'] = $request->input('content');

        if ($request->has('title')) 
            $updates['title'] = $request->input('title');

        if ($request->has('description')) 
            $updates['description'] = $request->input('description');

        if ($request->has('featured_image')) 
            $updates['featured_image'] = $request->input('featured_image');

        if ($request->has('canonical_url')) 
            $updates['canonical_url'] = $request->input('canonical_url');

        if ($request->has('code_head')) 
            $updates['code_head'] = $request->input('code_head');

        if ($request->has('code_foot')) 
            $updates['code_foot'] = $request->input('code_foot');

        $post = PostRepository::updatePost($postId, $updates);
        return response()->json(new PostOutputType($post, $blog, true));
    }

}