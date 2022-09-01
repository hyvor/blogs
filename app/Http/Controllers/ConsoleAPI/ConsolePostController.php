<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Post\PostObject;
use App\Data\Objects\ConsoleAPI\Post\PostVariantObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\App\ConsoleApi\ConsoleApiAccessingUser;
use App\Models\Blog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class ConsolePostController extends Controller
{
    public function getPosts(Request $request, Blog $blog, User $user)
    {
        $request->validate([
            'status' => 'string|in:featured,published,draft,scheduled',
            'author_id' => 'integer',
            'tag_id' => 'integer',
            'start_timestamp' => 'integer',
            'end_timestamp' => 'integer',
        ]);

        $status = $request->input('status');
        $authorId = $request->input('author_id');
        $tagId = $request->input('tag_id');

        $startTimestamp = $request->input('start_timestamp');
        $endTimestamp = $request->input('end_timestamp');
        $search = $request->input('search');

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

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
        $pages = PostRepository::getPages($blog)
            ->map(function ($page) use ($blog) {
                return new PostObject($page, $blog);
            });

        return response()->json($pages);
    }

    public function createPost(Request $request, Blog $blog, ConsoleApiAccessingUser $consoleApiAccessingUser)
    {
        $isPage = (bool) $request->input('is_page');
        $post = PostRepository::createPost($blog, $isPage);

        PostTagAuthorRepository::createAuthor($post->id, $consoleApiAccessingUser->user->id);

        $post->refresh();

        return response()->json(new PostObject($post, $blog));
    }

    // Get all the post from the database
    public function getPost(Request $request, Blog $blog)
    {
        $postId = (int) $request->route('id');
        $post = PostRepository::getPostById($postId);

        if (! $post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(new PostObject($post, $blog));
    }

    public function deletePost(Post $post)
    {
        PostRepository::deletePost($post);
    }

    public function updatePost(Request $request, Blog $blog, Post $post)
    {
        $request->validate([
            'slug' => 'string|max:255|nullable',
            'is_featured' => 'boolean',
            'canonical_url' => 'string|max:255|nullable',
            'featured_image_url' => 'string|max:255|nullable',
            'code_head' => 'string|nullable',
            'code_foot' => 'string|nullable',
        ]);

        $postUpdates = [];
        $postUpdatables = [
            'slug',
            'is_featured',
            'canonical_url',
            'featured_image_url',
            'code_head',
            'code_foot',
        ];

        foreach ($postUpdatables as $postUpdatable) {
            if ($request->has($postUpdatable)) {
                $postUpdates[$postUpdatable] = $request->input($postUpdatable);
            }
        }

        if (count($postUpdates) > 0) {
            PostRepository::updatePost($post, $postUpdates);
        }

        $post->refresh();

        return response()->json(new PostObject($post, $blog));
    }

    public function createPostVariant(Request $request, Blog $blog, Post $post)
    {
        $request->validate([
            'language_id' => 'required|integer',
        ]);

        $languageId = (int) $request->input('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (! $language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $language->id);

        if ($variant) {
            throw new TrustedException('Variant already exists', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::createPostVariant($post, $language);

        return response()->json(new PostVariantObject($variant, $variant->post, $blog));
    }

    public function updatePostVariant(Request $request, Blog $blog, Post $post)
    {
        $request->validate([
            'language_id' => 'required|integer',
            'status' => 'string|in:draft,published,scheduled',
            'content' => 'string|nullable',
            'content_unsaved' => 'string|nullable',
            'title' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',
        ]);

        $languageId = (int) $request->input('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (! $language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $languageId);

        if (! $variant) {
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        $variantUpdateables = [
            'status',
            'content',
            'content_unsaved',
            'title',
            'description',
        ];

        $variantUpdates = [];

        foreach ($variantUpdateables as $updateable) {
            if ($request->has($updateable)) {
                $variantUpdates[$updateable] = $request->input($updateable);
            }
        }

        if (count($variantUpdates) > 0) {
            PostRepository::updatePostVariant($post, $language, $variantUpdates);
        }

        $variant->refresh();

        return response()->json(new PostVariantObject($variant, $variant->post, $blog));
    }

    public function deletePostVariant(Request $request, Blog $blog, Post $post)
    {
        $request->validate([
            'language_id' => 'required|integer',
        ]);

        $languageId = (int) $request->input('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (! $language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        if ($language->is_primary) {
            throw new TrustedException(
                'Primary language variant cannot be deleted. Delete the post instead',
                TrustedException::ERROR_UNPROCESSABLE
            );
        }

        PostRepository::deletePostVariant($post, $languageId);

        return response()->json();
    }

    public function updateTags(Request $request, Blog $blog, Post $post)
    {
        $request->validate([
            'ids' => 'array',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids');

        PostTagAuthorRepository::updateTags($post, $ids);
    }

    public function updateAuthors(Request $request, Post $post)
    {
        $request->validate([
            'ids' => 'array',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids');

        PostTagAuthorRepository::updateAuthors($post, $ids);
    }
}
