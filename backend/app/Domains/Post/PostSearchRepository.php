<?php declare(strict_types=1);

namespace App\Domains\Post;

use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Collection;

class PostSearchRepository
{

    public const FILTERABLE_ATTRIBUTES = [
        'blog_id',
        'language_id',
        'is_published',
        'is_page',
    ];

    /**
     * These are ordered by relevancy
     */
    public const SEARCHABLE_ATTRIBUTES = [
        'title',
        'description',
        'content',
        'slug',
    ];

    /**
     * Better to use named arguments when using this function
     * @return CollectionWithTotal<Post>
     */
    public static function search(
        Blog $blog,
        Language $language,
        string $search,
        int $limit,
        int $offset,
        bool $isPage,
        /**
         * null = don't care
         * true = only published
         * false = only unpublished
         */
        ?bool $isPublished = null
    ): CollectionWithTotal {

        $post_variants = PostVariant::whereRaw("searchtext @@ to_tsquery('english', ?)", ["$search:*"])
            ->where('language_id', $language->id)
            ->when($isPublished, function ($query) {
                $query->where('status', 'published');
            })
            ->limit($limit)
            ->offset($offset)
            ->get();


        if (count($post_variants) > 0) {
            $postIds = $post_variants->pluck('post_id')->toArray();
            $posts = Post::whereIn('id', $postIds)
                ->where('blog_id', $blog->id)
                ->where('is_page', $isPage ? 'true' : 'false')
                
                ->get();
        } else {
            /** @var Collection<int, Post> $posts */
            $posts = collect();
        }

        return new CollectionWithTotal($posts, count($posts));
    }

}
