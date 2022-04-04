<?php

namespace App\Http\Controllers\DataAPI;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Content\PostSearchRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Domains\Post\PostRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;

class DataAPIController extends Controller
{
    public function post(Request $request, Blog $blog)
    {

        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $post = PostRepository::getPostByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }

        // Data API only return published posts
        if ($post->status !== 'published') {
            throw new TrustedException('This post is not yet published', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(DataAPIKeysFilter::filter(new PostObject($post, $blog), $keys));
    }

    public function tag(Request $request, Blog $blog)
    {
        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $tag = TagRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);
        if (!$tag) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(DataAPIKeysFilter::filter(new TagObject($tag, $blog), $keys));
    }

    public function author(Request $request, Blog $blog)
    {
        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $user = UserRepository::getUserByBlogIdAndIdentifier($blog->id, $id, $slug);
        if (!$user) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }
        if (!$user->posts_count > 0) {
            throw new TrustedException('This user is not an author', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(DataAPIKeysFilter::filter(new AuthorObject($user, $blog), $keys));
    }

    public function posts(Request $request, Blog $blog)
    {

        $limit = $request->input('limit') ?? 25;
        $page = $request->input('page') ?? 1;
        $filter = $request->input('filter');
        $sort = $request->input('sort');
        $keys = $request->input('keys');

        // check if the sort is valid
        $sort ??= 'published_at DESC';
        @[ $orderBy, $orderMethod ] = explode(' ', $sort);
        $orderMethod ??= 'DESC';

        if (!in_array($orderBy, 
            [
                'published_at', 'created_at', 'updated_at', 
                'is_featured', 'title', 'reading_time'
            ]
            )
        ) {
            throw new TrustedException("Sort method $orderBy not supported", TrustedException::ERROR_BAD_REQUEST);
        }

        $orderMethod = strtoupper($orderMethod);
        if (!in_array($orderMethod, ['ASC', 'DESC'])) {
            $orderMethod = 'DESC';
        }

        $posts = PostRepository::getPostsWithFilterQ(
            $blog->id,
            $filter,
            $limit,
            $page - 1 * $limit,
            $orderBy,
            $orderMethod
        )->map(function ($post) use ($blog) {
            return new PostObject($post, $blog);
        });

        $filteredPosts = DataAPIKeysFilter::filter($posts, $keys);

        return response()->json([
            'data' => $filteredPosts,
            'count' => null
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
            'count' => null
        ]);

    }

}
