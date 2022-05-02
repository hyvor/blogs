<?php
namespace App\Http\Controllers\DataApi;

use App\Data\Objects\DataAPI\PaginationObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Tag\TagRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    
    const ALLOWED_SORTS = [
        'posts_count' => 'tags.posts_count',
        'created_at' => 'tags.created_at'
    ];

    public function tag(Request $request, Blog $blog)
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
        
        $tag = TagRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$tag) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(
            KeysFilter::filter(new TagObject($tag, $blog, $language), $keys)
        );

    }

    public function tags(Request $request, Blog $blog)
    {

        $request->validate([
            'language' => 'string',
            'limit' => 'int|min:1',
            'page' => 'int|min:1',
            'filter' => 'string',
            'sort' => 'string',
            'keys' => 'string'
        ]);

        $language = Helper::getLanguage($blog, $request->input('language'));
        $limit = Helper::getLimit($request->input('limit'));
        $page = Helper::getPage($request->input('page'));
        $offset = Helper::getOffset($page, $limit);
        $filter = $request->input('filter');
        $keys = $request->input('keys');
        $orderBys = Helper::getSort($request->input('sort'), self::ALLOWED_SORTS);
        
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
            'pagination' => new PaginationObject($limit, $page, $data->total)
        ]);

    }

}
