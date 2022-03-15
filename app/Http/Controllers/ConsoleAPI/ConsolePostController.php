<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\PostObject;
use App\Data\Params\ConsoleAPI\PostsFilterParam;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Domains\Post\PostRepository;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ConsolePostController extends Controller
{

    public function getPosts(Request $request, Blog $blog, User $user)
    {

        $request->validate([
            'filters' => 'required|json'
        ]);

        $filters = json_decode($request->input('filters'));

        $language = LanguageRepository::getLanguageById($blog, $filters->language);

        $posts = PostRepository::getPosts(
            $blog,
            $language,
            (new PostsFilterParam())
                ->setStatus($filters->status === 'all' ? null : $filters->status)
                ->setAuthorId($filters->author === 'all' ? null : $filters->author)
                ->setTagId($filters->tag === 'all' ? null : $filters->tag)
                ->setStartTimestamp($filters->dateStart)
                ->setEndTimestamp($filters->dateEnd)
                ->setSearch($filters->search),
            $request->input('limit'),
            $request->input('offset') ?? 0
        )->map(function ($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });

        return response()->json($posts);
    }

    public function getPages(Blog $blog)
    {

        $pages = PostRepository::getPages($blog->id)
            ->map(function ($page) use ($blog) {
                return new PostObject($page, $blog);
            });

        return response()->json($pages);

    }

    public function createPost(Request $request, Blog $blog)
    {
        $isPage = (bool) $request->input('is_page');
        $post = PostRepository::createPost($blog->id, $isPage);
        return response()->json(new PostObject($post, $blog));
    }

    public function getPost(Request $request, Blog $blog)
    {
        $postId = (int) $request->route('id');
        $post = PostRepository::getPostById($postId);
        return response()->json(new PostObject($post, $blog));
    }

    public function deletePost(Request $request, Blog $blog)
    {
        $postId = $request->route('id');
        PostRepository::deletePost($postId, $blog->id);
    }

    public function updatePost(Request $request, Blog $blog)
    {

        $postId = $request->route('id');
        $updates = [];

        /**
         * Some strings become null when empty
         * So, always use ->has() to check if the variable is set
         */
        if ($request->has('published_at')) {
            $updates['published_at'] = $request->input('published_at');
        }

        if ($request->has('status')) {
            $updates['status'] = $request->input('status');
        }

        if ($request->has('is_featured')) {
            $updates['is_featured'] = (bool) $request->input('is_featured');
        }

        if ($request->has('slug')) {
            $updates['slug'] = $request->input('slug');
        }

        if ($request->has('content')) {
            $updates['content'] = $request->input('content');
        }

        if ($request->has('content_unsaved')) {
            $updates['content_unsaved'] = $request->input('content_unsaved');
        }

        if ($request->has('title')) {
            $updates['title'] = $request->input('title');
        }

        if ($request->has('description')) {
            $updates['description'] = $request->input('description');
        }

        if ($request->has('featured_image')) {
            $updates['featured_image'] = $request->input('featured_image');
        }

        if ($request->has('canonical_url')) {
            $updates['canonical_url'] = $request->input('canonical_url');
        }

        if ($request->has('code_head')) {
            $updates['code_head'] = $request->input('code_head');
        }

        if ($request->has('code_foot')) {
            $updates['code_foot'] = $request->input('code_foot');
        }

        $post = PostRepository::updatePost($postId, $updates);
        return response()->json(new PostObject($post, $blog));
    }
}
