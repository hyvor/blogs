<?php

namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Data\Objects\DataAPI\PaginationObject;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class AuthorsController extends Controller
{
    public const ALLOWED_SORTS = [
        'posts_count' => 'users.posts_count',
        'created_at' => 'users.created_at',
    ];

    public function author(Request $request, Blog $blog)
    {
        $request->validate([
            'id' => 'int|required_without:slug',
            'slug' => 'string|required_without:id',
            'language' => 'string',
            'keys' => 'string',
        ]);

        $id = $request->input('id');
        $slug = $request->input('slug');
        $language = Helper::getLanguage($blog, $request->input('language'));
        $keys = $request->input('keys');

        $author = UserRepository::getUserByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (! $author) {
            throw new TrustedException('Author not found', TrustedException::ERROR_NOT_FOUND);
        }

        // must have written one post to be an author
        // otherwise, it can be a user like finance
        if ($author->posts_count === 0) {
            throw new TrustedException('User is not an author', TrustedException::ERROR_UNPROCESSABLE);
        }

        return response()->json(
            KeysFilter::filter(new AuthorObject($author, $blog, $language), $keys)
        );
    }

    public function authors(Request $request, Blog $blog)
    {
        $request->validate([
            'language' => 'string',
            'limit' => 'int|min:1',
            'page' => 'int|min:1',
            'filter' => 'string',
            'sort' => 'string',
            'keys' => 'string',
        ]);

        $language = Helper::getLanguage($blog, $request->input('language'));
        $limit = Helper::getLimit($request->input('limit'));
        $page = Helper::getPage($request->input('page'));
        $offset = Helper::getOffset($page, $limit);
        $filter = $request->input('filter');
        $keys = $request->input('keys');
        $orderBys = Helper::getSort($request->input('sort'), self::ALLOWED_SORTS);

        $data = UserRepository::getAuthorsWithFilterQ(
            blog: $blog,
            filter: $filter,
            limit: $limit,
            offset: $offset,
            orderBys: $orderBys,
        );

        $authors = $data->collection->map(function ($author) use ($blog, $language) {
            return new AuthorObject($author, $blog, $language);
        });

        $filteredAuthors = KeysFilter::filter($authors, $keys);

        return response()->json([
            'data' => $filteredAuthors,
            'pagination' => new PaginationObject($limit, $page, $data->total),
        ]);
    }
}
