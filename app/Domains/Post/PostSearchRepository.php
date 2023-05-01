<?php declare(strict_types=1);

namespace App\Domains\Post;

use App;
use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\PostContentRepository;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Collection;
use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Search\SearchResult;

class PostSearchRepository
{

    private const FILTERABLE_ATTRIBUTES = [
        'blog_id',
        'language_id',
        'is_published',
        'is_page',
    ];

    /**
     * These are ordered by relevancy
     */
    private const SEARCHABLE_ATTRIBUTES = [
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
        ?bool $isPublished = null
    ): CollectionWithTotal {
        $index = self::getIndex();

        $conditions = [
            'blog_id' => $blog->id,
            'language_id' => $language->id,
            'is_page' => $isPage,
        ];
        if ($isPublished !== null) {
            $conditions['is_published'] = $isPublished;
        }

        $filter = self::getSearchFilter($conditions);

        /**
         * @var SearchResult $results
         */
        $results = $index->search($search, [
            'limit' => $limit,
            'offset' => $offset,
            'filter' => $filter,
            'attributesToRetrieve' => ['post_id'],
        ]);

        /** @var array<array{post_id: int}> $hits */
        $hits = $results->getHits();

        if (count($hits) > 0) {
            $postIds = collect($hits)->map(fn ($hit) => $hit['post_id'])->all();
            $postIdsForField = implode(',', $postIds);
            $posts = Post::whereIn('id', $postIds)
                ->orderByRaw("FIELD(id, $postIdsForField)")
                ->get();
        } else {
            /** @var Collection<int, Post> $posts */
            $posts = collect();
        }

        return new CollectionWithTotal($posts, $results->getEstimatedTotalHits());
    }

    /**
     * from https://github.com/laravel/scout/blob/9.x/src/Engines/MeiliSearchEngine.php
     *
     * @param array<string, mixed> $conditions
     */
    private static function getSearchFilter($conditions) : string
    {
        $filters = collect($conditions)->map(function ($value, $key) {
            if (is_bool($value)) {
                return sprintf('%s=%s', $key, $value ? 'true' : 'false');
            }

            return is_numeric($value)
                            ? sprintf('%s=%s', $key, $value)
                            : sprintf('%s="%s"', $key, $value); // @phpstan-ignore-line
        });

        return $filters->values()->implode(' AND ');
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
            'content' => $postVariant->content ? PostContentRepository::getText($postVariant->content, $blog) : '',
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

    /**
     * Updates filterable attributes
     * https://docs.meilisearch.com/learn/advanced/filtering_and_faceted_search.html
     *
     * This should be called when attributes change
     */
    public static function setFilterableAttributes(): void
    {
        if (self::isMeilisearch()) {
            self::getIndex()->updateFilterableAttributes(self::FILTERABLE_ATTRIBUTES);
        }
    }

    public static function setSearchableAttributes(): void
    {
        if (self::isMeilisearch()) {
            self::getIndex()->updateSearchableAttributes(self::SEARCHABLE_ATTRIBUTES);
        }
    }

    public static function resetIndex() : void
    {
        self::getClient()->getIndex(self::getIndexName())->delete();
        self::getClient()->createIndex(self::getIndexName());
        self::setFilterableAttributes();
        self::setSearchableAttributes();
    }

    private static function getClient() : Client
    {
        $host = strval(config('scout.meilisearch.host'));
        $key = strval(config('scout.meilisearch.key'));
        return new Client($host, $key);
    }

    private static function getIndex() : Indexes
    {
        $client = self::getClient();
        return $client->index(self::getIndexName());
    }

    public static function getIndexName() : string
    {
        return App::environment('testing') ? 'posts_testing' : 'posts';
    }

    private static function isMeilisearch() : bool
    {
        return config('scout.driver') === 'meilisearch';
    }
}
