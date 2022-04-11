<?php

namespace App\Http\Controllers\DataAPI;

use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Domains\Post\PostRepository;

class DataAPIPostsController extends Controller
{
    public function post(Request $request, Blog $blog)
    {

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
            'language' => 'string'
        ]);

        $id = $request->input('id');
        $slug = $request->input('slug');
        $language = DataAPIHelper::getLanguage($blog, $request->input('language'));
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
            throw new TrustedException('This post is not yet published', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(
            DataAPIKeysFilter::filter(new PostObject($post, $blog, $language), $keys)
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

        $language = DataAPIHelper::getLanguage($blog, $request->input('language'));
        $limit = DataAPIHelper::getLimit($request->input('limit'));
        $page = DataAPIHelper::getPage($request->input('page'));
        $offset = DataAPIHelper::getOffset($page, $limit);
        $filter = $request->input('filter');
        $keys = $request->input('keys');
        $sort = $request->input('sort');
        $pages = (bool) $request->input('pages');

        $orderBys  = DataAPIHelper::getSort(
            $sort,
            [
                'published_at' => 'posts.published_at',
                'created_at' => 'posts.created_at',
                'updated_at' => 'posts.updated_at',
                'is_featured' => 'posts.is_featured',
                'title' => 'post_variants.title',
                'words' => 'post_variants.words'
            ]
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
        
        $posts = $data['posts']->map(function ($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });
        $total = $data['total'];

        $filteredPosts = DataAPIKeysFilter::filter($posts, $keys);

        return response()->json([
            'data' => $filteredPosts,
            'pagination' => new PaginationObject($limit, $page, $total)
        ]);

    }

    public function postsSearch(Request $request, Blog $blog)
    {

        $request->validate([
            'search' => 'string|required',
            'language' => 'string',
            'limit' => 'int',
            'page' => 'int'
        ]);

        $search = $request->input('search');
        $languageCode = $request->input('language') ?? null;
        $language = $languageCode ? 
            LanguageRepository::getLanguageByCode($blog, $languageCode) : 
            LanguageRepository::getPrimaryLanguage($blog);

        $limit = $request->input('limit') ?? 20;
        $page = $request->input('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $searchData = PostSearchRepository::search(
            search: $search,
            limit: $limit,
            offset: $offset,
            blogId: $blog->id,
            languageId: $language->id,
            isPage: false,
            isPublished: true,
        );

        $posts = $searchData['posts']->map(function($post) use ($blog, $language) {
            return new PostObject($post, $blog, $language);
        });
        
        return response()->json([
            'data' => $posts,
            'pagination' => new PaginationObject($limit, $page, $searchData[''])
        ]);

    }

}
