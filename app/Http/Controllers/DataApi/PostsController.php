<?php

namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Domains\Post\PostRepository;

class PostsController extends Controller
{

    const ALLOWED_SORTS = 
    [
        'published_at' => 'posts.published_at',
        'created_at' => 'posts.created_at',
        'id' => 'posts.id',
        'updated_at' => 'posts.updated_at',
        'is_featured' => 'posts.is_featured',
        'title' => 'post_variants.title',
        'words' => 'post_variants.words'
    ];

    public function post(Request $request, Blog $blog)
    {

        $request->validate([
            'id' => 'int|required_without:slug',
            'slug' => 'string|required_without:id',
            'language' => 'string',
            'keys' => 'string'
        ]);

        $id = $request->input('id');
        $slug = $request->input('slug');
        $language = Helper::getLanguage($blog, $request->input('language'));
        $keys = $request->input('keys');


        $post = PostRepository::getPostByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $language->id);

        if (!$variant) {
            throw new TrustedException('Post variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        // Data API only return published posts
        if ($variant->status !== 'published') {
            throw new TrustedException('This post is not yet published', TrustedException::ERROR_INVALID_INPUT);
        }

        return response()->json(
            KeysFilter::filter(new PostObject($post, $blog, $language), $keys)
        );

    }

    public function posts(Request $request, Blog $blog)
    {

        $request->validate([
            'language' => 'string',
            'limit' => 'int|min:1',
            'page' => 'int|min:1',
            'filter' => 'string',
            'sort' => 'string',
            'keys' => 'string',
            'pages' => 'boolean'
        ]);

        $language = Helper::getLanguage($blog, $request->input('language'));
        $limit = Helper::getLimit($request->input('limit'));
        $page = Helper::getPage($request->input('page'));
        $offset = Helper::getOffset($page, $limit);
        $filter = $request->input('filter');
        $keys = $request->input('keys');
        $sort = $request->input('sort');
        $pages = (bool) $request->input('pages');

        $orderBys  = Helper::getSort(
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
            'pagination' => new PaginationObject($limit, $page, $data->total)
        ]);

    }

    public function postsSearch(Request $request, Blog $blog)
    {

        $request->validate([
            'search' => 'string|required',
            'language' => 'string',
            'limit' => 'int',
            'page' => 'int',
            'keys' => 'string'
        ]);

        $search = $request->input('search');
        $language = Helper::getLanguage($blog, $request->input('language'));

        $limit = Helper::getLimit($request->input('limit'));
        $page = Helper::getPage($request->input('page'));
        $offset = Helper::getOffset($page, $limit);
        $keys = $request->input('keys');

        $searchData = PostSearchRepository::search(

            blog: $blog,
            language: $language,
            search: $search,
            limit: $limit,
            offset: $offset,
            isPage: false,
            isPublished: true,
            
        );

        $posts = $searchData->collection->map(function($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });

        $filteredPosts = KeysFilter::filter($posts, $keys);
        
        return response()->json([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $searchData->total)
        ]);

    }

}
