<?php
declare(strict_types=1);

namespace App\Http\Controllers\DataApi;

use App\Data\Enums\PostStatusEnum;
use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostSearchRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public const ALLOWED_SORTS =
        [
            'published_at' => 'posts.published_at',
            'created_at' => 'posts.created_at',
            'id' => 'posts.id',
            'updated_at' => 'posts.updated_at',
            'is_featured' => 'posts.is_featured',
            'title' => 'post_variants.title',
            'words' => 'post_variants.words',
        ];

    public function post(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'id' => 'int|required_without:slug',
            'slug' => 'string|required_without:id',
            'language' => 'string',
            'keys' => 'string',
        ]);

        $id = $request->has('id') ? $request->integer('id') : null;
        $slug = $request->has('slug') ? (string)$request->string('slug') : null;
        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string)$request->string('language') : null
        );
        $keys = $request->has('keys') ? (string)$request->string('keys') : null;

        $post = null;
        if ($id) {
            $post = PostRepository::getPostById($id);
        } elseif ($slug) {
            $post = PostRepository::getPostByLanguageAndSlug($language, $slug);
        }

        if (!$post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $language->id);

        if (!$variant) {
            throw new TrustedException('Post variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        // Data API only return published posts
        if ($variant->status !== PostStatusEnum::PUBLISHED) {
            throw new TrustedException('This post is not yet published', TrustedException::ERROR_UNPROCESSABLE);
        }

        return response()->json(
            KeysFilter::filter(new PostObject($post, $blog, $language), $keys)
        );
    }

    public function posts(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'language' => 'string',
            'limit' => 'int|min:1',
            'page' => 'int|min:1',
            'filter' => 'string',
            'sort' => 'string',
            'keys' => 'string',
            'pages' => 'boolean',
        ]);

        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string)$request->string('language') : null
        );
        $limit = Helper::getLimit($request->has('limit') ? $request->integer('limit') : null);
        $page = Helper::getPage($request->has('page') ? $request->integer('page') : null);
        $offset = Helper::getOffset($page, $limit);
        $filter = $request->has('filter') ? (string)$request->string('filter') : null;
        $keys = $request->has('keys') ? (string)$request->string('keys') : null;
        $sort = $request->has('sort') ? (string)$request->string('sort') : null;
        $pages = $request->boolean('pages');

        $orderBys = Helper::getSort(
            $sort,
            self::ALLOWED_SORTS
        );

        $data = PostRepository::getPostsWithFilterQ(
            blog: $blog,
            language: $language,
            filter: $filter,
            limit: $limit,
            offset: $offset,
            orderBys: $orderBys,
            isPages: $pages
        );

        $posts = $data->collection->map(function ($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });

        $filteredPosts = KeysFilter::filter($posts, $keys);

        return response()->json([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $data->total),
        ]);
    }

    public function postsSearch(
        Request $request,
        Blog $blog,
        PostSearchRepository $postSearchRepository
    ): JsonResponse {
        $request->validate([
            'search' => 'string|required',
            'language' => 'string',
            'limit' => 'int',
            'page' => 'int',
            'keys' => 'string',
        ]);

        $search = (string)$request->string('search');

        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string)$request->string('language') : null
        );
        $limit = Helper::getLimit($request->has('limit') ? $request->integer('limit') : null);
        $page = Helper::getPage($request->has('page') ? $request->integer('page') : null);
        $offset = Helper::getOffset($page, $limit);
        $keys = $request->has('keys') ? (string)$request->string('keys') : null;

        $searchData = $postSearchRepository->search(
            blog: $blog,
            language: $language,
            search: $search,
            limit: $limit,
            offset: $offset,
            isPage: false,
            isPublished: true,
        );

        $posts = $searchData->collection->map(function ($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });

        $filteredPosts = KeysFilter::filter($posts, $keys);

        return response()->json([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $searchData->total),
        ]);
    }
}
