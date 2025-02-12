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
         * null = any type
         * true = only published
         */
        null|true $isPublished = null
    ): CollectionWithTotal {

        $searchQuery = implode(' & ', explode(' ', $search));

        /**
         * We are using websearch_to_tsquery since most of the calls are from end users
         * searching for posts
         */
        $postVariants = PostVariant::whereRaw("ts @@ websearch_to_tsquery(ts_language, ?)", [$searchQuery])
            ->orderByRaw("ts_rank(ts, websearch_to_tsquery(ts_language, ?)) DESC", [$searchQuery])
            ->where('language_id', $language->id)
            ->when($isPublished, function ($query) {
                $query->where('status', 'published');
            })
            ->select('post_id')
            ->join('posts', 'post_variants.post_id', '=', 'posts.id')
            ->where('posts.blog_id', $blog->id)
            ->where('is_page', $isPage)
            ->limit($limit)
            ->offset($offset)
            ->get();

        if (count($postVariants) > 0) {
            $postIds = $postVariants->pluck('post_id')->toArray();
            $posts = Post::whereIn('id', $postIds)->get();
        } else {
            /** @var Collection<int, Post> $posts */
            $posts = collect();
        }

        return new CollectionWithTotal($posts, count($posts));
    }

}
