<?php declare(strict_types=1);

namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Tag\TagRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public const ALLOWED_SORTS = [
        'posts_count' => 'tags.posts_count',
        'created_at' => 'tags.created_at',
    ];

    public function tag(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'id' => 'int|required_without:slug',
            'slug' => 'string|required_without:id',
            'language' => 'string',
            'keys' => 'string',
        ]);

        $id = $request->has('id') ? $request->integer('id') : null;
        $slug = $request->has('slug') ? (string) $request->str('slug') : null;
        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string) $request->string('language') : null
        );
        $keys = $request->has('keys') ? (string) $request->string('keys') : null;

        $tag = TagRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$tag) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(
            KeysFilter::filter(new TagObject($tag, $blog, $language), $keys)
        );
    }

    public function tags(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'language' => 'string',
            'limit' => 'int|min:1',
            'page' => 'int|min:1',
            'filter' => 'string',
            'sort' => 'string',
            'keys' => 'string',
        ]);

        $language = Helper::getLanguage(
            $blog,
            $request->has('language') ? (string) $request->string('language') : null
        );
        $limit = Helper::getLimit($request->has('limit') ? $request->integer('limit') : null);
        $page = Helper::getPage($request->has('page') ? $request->integer('page') : null);
        $offset = Helper::getOffset($page, $limit);
        $filter = $request->has('filter') ? (string) $request->string('filter') : null;
        $keys = $request->has('keys') ? (string) $request->string('keys') : null;
        $sort = $request->has('sort') ? (string) $request->string('sort') : null;;
        $orderBys = Helper::getSort($sort, self::ALLOWED_SORTS);

        $data = TagRepository::getTagsWithFilterQ(
            blog: $blog,
            filter: $filter,
            limit: $limit,
            offset: $offset,
            orderBys: $orderBys,
        );

        $tags = $data->collection->map(function ($tag) use ($blog, $language) {
            return new TagObject($tag, $blog, $language);
        });

        $filteredTags = KeysFilter::filter($tags, $keys);

        return response()->json([
            'data' => $filteredTags,
            'pagination' => new PaginationObject($limit, $page, $data->total),
        ]);
    }
}
