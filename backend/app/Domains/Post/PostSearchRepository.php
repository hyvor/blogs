<?php declare(strict_types=1);

namespace App\Domains\Post;

use App;
use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\PostContentService;
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

        $conditions = [
            'blog_id' => $blog->id,
            'language_id' => $language->id,
            'is_page' => $isPage,
        ];
        if ($isPublished !== null) {
            $conditions['is_published'] = $isPublished;
        }

        $post_variants = PostVariant::search($search)
            ->where('language_id', $language->id)
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

    /**
     * @return array<string, mixed>
     */
    public static function getSearchDocument(PostVariant $postVariant): array
    {

        /** @var Post $post */
        $post = $postVariant->post;
        /** @var Blog $blog */
        $blog = $post->blog;

        return [

            // identifier
            'id' => $postVariant->id,
            'post_id' => $postVariant->post_id,

            /**
             * Search data
             */
            'title' => $postVariant->title,
            'description' => $postVariant->description,
            'content' => $postVariant->content ? PostContentService::getText($postVariant->content, $blog) : '',
            'slug' => $postVariant->slug,

            /**
             * Reference and filtering
             */
            'blog_id' => $blog->id,
            'language_id' => $postVariant->language_id,
            'is_published' => $postVariant->status === PostStatusEnum::PUBLISHED, // search only needs to know if the post is published
            // (data API vs console API search)
            'is_page' => $post->is_page,

        ];
    }

    public static function getIndexName() : string
    {
        return App::environment('testing') ? 'posts_testing' : 'posts';
    }

}
