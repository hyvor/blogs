<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Post\PostObject;
use App\Data\Objects\ConsoleAPI\Post\PostVariantObject;
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
            'status' => 'string',
            'author_id' => 'integer',
            'tag_id' => 'integer',
            'start_timestamp' => 'integer',
            'end_timestamp' => 'integer',
        ]);

        $filters = json_decode($request->input('filters'));

        $status = $request->input('status');
        $authorId = $request->input('author_id');
        $tagId = $request->input('tag_id');

        $startTimestamp = $request->input('start_timestamp');
        $endTimestamp = $request->input('end_timestamp');
        $search = $request->input('search');

        $limit = 50;
        $offset = $request->input('offset') ?? 0;

        $posts = PostRepository::getPosts(

            $blog,

            $status,

            $authorId,
            $tagId,

            $startTimestamp,
            $endTimestamp,

            $search,

            $limit,
            $offset

        )->map(function ($post) use ($blog) {

            return new PostObject($post, $blog);

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

    // Get all the post from the database
    public function getPost(Request $request, Blog $blog)
    {
        $postId = (int) $request->route('id');
        $post = PostRepository::getPostById($postId);
        return response()->json(new PostObject($post, $blog));
    }

    public function deletePost(Request $request)
    {
        $postId = $request->route('id');
        PostRepository::deletePost($postId);
    }

    public function updatePost(Request $request, Blog $blog)
    {

        $postId = $request->route('id');

        $postUpdates = [];
        $postUpdatables = [
            'slug',
            'is_featured',
            'canonical_url',
            'code_head',
            'code_foot'
        ];

        foreach ($postUpdatables as $postUpdatable) {
            if ($request->has($postUpdatable)) {
                $postUpdates[$postUpdatable] = $request->input($postUpdatable);
            }
        }

        if (count($postUpdates) > 0) {
            PostRepository::updatePost($postId, $postUpdates);
        }

        $variants = $request->input('variants');

        if (is_array($variants)) {
            foreach ($variants as $languageId => $variantUpdates) {
                PostRepository::updatePostVariant($postId, $languageId, $variantUpdates);
            }
        }

        $post = PostRepository::getPostById($postId);

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

        if ($request->has('tag')) {
            $updates['tag'] = $request->input('tag');
        }

        // if ($request->has('author')) {
        //     $updates['author'] = $request->input('author');
        // }

        $post = PostRepository::updatePost($postId, $updates);

        return response()->json(new PostObject($post, $blog));
    }


    public function createPostVariant(Request $request, Blog $blog)
    {
        $postId = $request->route('id');
        $languageId = $request->input('language_id');

        $variant = PostRepository::createPostVariant($postId, $languageId);

        return response()->json(new PostVariantObject($variant, $variant->post, $blog));
    }

    public function deletePostVariant(Request $request)
    {
        $postId = $request->route('id');
        $languageId = $request->input('language_id');

        PostRepository::deletePostVariant($postId, $languageId);
    }
}
