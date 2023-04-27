<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\PostStatusEnum;
use App\Data\Objects\ConsoleAPI\Post\PostObject;
use App\Data\Objects\ConsoleAPI\Post\PostVariantObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostSearchRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\App\ConsoleApi\ConsoleApiAccessingUser;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsolePostController extends Controller
{
    public function getPosts(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'status' => 'string|in:featured,published,draft,scheduled',
            'author_id' => 'integer',
            'tag_id' => 'integer',
            'start_timestamp' => 'integer',
            'end_timestamp' => 'integer',
            'search' => 'string|nullable',
            'limit' => 'integer|max:100',
            'offset' => 'integer',
        ]);

        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset');

        $search = (string) $request->string('search');

        if ($search) {

            $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);
            $posts = PostSearchRepository::search(
                $blog,
                $primaryLanguage,
                $search,
                $limit,
                $offset,
                false,
            )
                ->collection
                ->map(fn ($post) => new PostObject($post, $blog));

            return response()->json($posts);

        }

        $status = $request->has('status') ? (string) $request->string('status') : null;
        $authorId = $request->has('author_id') ? $request->integer('author_id') : null;
        $tagId = $request->has('tag_id') ? $request->integer('tag_id') : null;

        $startTimestamp = $request->has('start_timestamp') ? $request->integer('start_timestamp') : null;
        $endTimestamp = $request->has('end_timestamp') ? $request->integer('end_timestamp') : null;
        $search = $request->has('search') ? (string) $request->string('search') : null;

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

    public function getPages(Blog $blog) : JsonResponse
    {
        $pages = PostRepository::getPages($blog)
            ->map(function ($page) use ($blog) {
                return new PostObject($page, $blog);
            });

        return response()->json($pages);
    }

    public function createPost(Request $request, Blog $blog, ConsoleApiAccessingUser $consoleApiAccessingUser) : JsonResponse
    {
        $isPage = (bool) $request->input('is_page');
        $post = PostRepository::createPost($blog, $isPage);

        PostTagAuthorRepository::createAuthor($post->id, $consoleApiAccessingUser->user->id);

        $post->refresh();

        return response()->json(new PostObject($post, $blog));
    }

    // Get all the post from the database
    public function getPost(Request $request, Blog $blog) : JsonResponse
    {
        $postId = intval($request->route('id'));
        $post = PostRepository::getPostById($postId);

        if (! $post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(new PostObject($post, $blog));
    }

    public function deletePost(Post $post) : JsonResponse
    {
        PostRepository::deletePost($post);

        return response()->json();
    }

    public function updatePost(Request $request, Blog $blog, Post $post) : JsonResponse
    {
        $request->validate([
           // 'slug' => 'string|max:255|nullable',
            'is_featured' => 'boolean',
            'canonical_url' => 'string|max:255|nullable',
            'featured_image_url' => 'string|max:255|nullable',
            'code_head' => 'string|nullable',
            'code_foot' => 'string|nullable',
            'published_at' => 'integer'
        ]);

        $postUpdates = [];
        $postUpdatables = [
           // 'slug',
            'is_featured',
            'canonical_url',
            'featured_image_url',
            'code_head',
            'code_foot',
            'published_at'
        ];

        foreach ($postUpdatables as $postUpdatable) {
            if ($request->has($postUpdatable)) {
                $postUpdates[$postUpdatable] = $request->input($postUpdatable);
            }
        }

        if (count($postUpdates) > 0) {

            /*if (array_key_exists('slug', $postUpdates)) {
                $bySlugPost = PostRepository::getPostByBlogIdAndSlug($blog->id, strval($postUpdates['slug']));
                if ($bySlugPost && $bySlugPost->id !== $post->id) {
                    throw new TrustedException('Slug has already been taken');
                }
            }*/

            PostRepository::updatePost($post, $postUpdates);
        }

        $post->refresh();

        return response()->json(new PostObject($post, $blog));
    }

    public function createPostVariant(Request $request, Blog $blog, Post $post) : JsonResponse
    {
        $request->validate([
            'language_id' => 'required|integer',
        ]);

        $languageId = $request->integer('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (! $language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $language->id);

        if ($variant) {
            throw new TrustedException('Variant already exists', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::createPostVariant($post, $language);

        return response()->json(
            new PostVariantObject($variant, $post, $blog)
        );
    }

    public function updatePostVariant(Request $request, Blog $blog, Post $post) : JsonResponse
    {
        $request->validate([
            'language_id' => 'required|integer',
            'slug' => 'string|max:255|nullable',
            'status' => 'string|in:draft,published,scheduled',
            'content' => 'string|nullable',
            'content_unsaved' => 'string|nullable',
            'title' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',
        ]);

        $languageId = $request->integer('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (!$language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $languageId);

        if (!$variant) {
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        $variantUpdates = [];

        if ($request->has('slug'))
            $variantUpdates['slug'] = (string)$request->string('slug');

        if ($request->has('status'))
            $variantUpdates['status'] = PostStatusEnum::from((string)$request->string('status'));

        if ($request->has('content'))
            $variantUpdates['content'] = $request->input('content') !== null ?
                (string) $request->string('content') :
                null;

        if ($request->has('content_unsaved'))
            $variantUpdates['content_unsaved'] = $request->input('content_unsaved') !== null ?
                (string) $request->string('content_unsaved') :
                null;

        if ($request->has('title'))
            $variantUpdates['title'] = $request->input('title') !== null ?
                (string) $request->string('title') :
                null;

        if ($request->has('description'))
            $variantUpdates['description'] = $request->input('description') !== null ?
                (string) $request->string('description') :
                null;

        if (count($variantUpdates) > 0) {

            if (array_key_exists('slug', $variantUpdates)) {
                $bySlugPost = PostRepository::getPostByLanguageAndSlug($language, strval($variantUpdates['slug']));

                if ($bySlugPost && $bySlugPost->id !== $post->id) {
                    throw new TrustedException('Slug has already been taken');
                }
            }

            PostRepository::updatePostVariant($post, $language, $variantUpdates);
        }

        $variant->refresh();

        return response()->json(
            new PostVariantObject($variant, $post, $blog)
        );

    }

    public function deletePostVariant(Request $request, Blog $blog, Post $post) : JsonResponse
    {
        $request->validate([
            'language_id' => 'required|integer',
        ]);

        $languageId = $request->integer('language_id');
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

    public function updateTags(Request $request, Blog $blog, Post $post) : JsonResponse
    {
        $request->validate([
            'ids' => 'array',
            'ids.*' => 'integer',
        ]);

        /** @var int[] $ids */
        $ids = $request->input('ids');

        PostTagAuthorRepository::updateTags($post, $ids);

        return response()->json();
    }

    public function updateAuthors(Request $request, Post $post) : JsonResponse
    {
        $request->validate([
            'ids' => 'array',
            'ids.*' => 'integer',
        ]);

        /** @var int[] $ids */
        $ids = $request->input('ids');

        PostTagAuthorRepository::updateAuthors($post, $ids);

        return response()->json();
    }
}
