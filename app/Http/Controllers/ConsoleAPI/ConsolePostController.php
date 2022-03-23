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
            'filters' => 'required|json'
        ]);

        $filters = json_decode($request->input('filters'));

        $language = LanguageRepository::getLanguageById($blog, $filters->language_id);

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
