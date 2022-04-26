<?php
namespace App\Domains\Post;

use App\Domains\Post\Content\PostContentRepository;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use MeiliSearch\Client;

class PostSearchRepository 
{

    public const SEARCH_INDEX_NAME = 'posts';

    private const FILTERABLE_ATTRIBUTES = [
        'blog_id',
        'language_id',
        'is_published',
        'is_page'
    ];

    /**
     * These are ordered by relevancy
     */
    private const SEARCHABLE_ATTRIBUTES = [
        'title',
        'description',
        'content',
        'slug'
    ];

    /**
     * Better to use named arguments when using this function
     */
    public static function search(
        Blog $blog,
        Language $language,
        string $search,
        int $limit,
        int $offset,
        bool $isPage,
        ?bool $isPublished = null
    ) : CollectionWithTotal {

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

        $results = $index->search($search, [
            'limit' => $limit,
            'offset' => $offset,
            'filter' => $filter,
            'attributesToRetrieve' => ['post_id'],
        ]);

        $hits = $results->getHits();
        
        if (count($hits) > 0) {
            $postIds = collect($hits)->map(fn ($hit) => $hit['post_id'])->all();
            $postIdsForField = implode(',', $postIds);
            $posts = Post::whereIn('id', $postIds)
                ->orderByRaw("FIELD(id, $postIdsForField)")
                ->get();
        } else {
            $posts = collect([]);
        }
        
        return new CollectionWithTotal($posts, $results->getNbHits());

    }


    // from https://github.com/laravel/scout/blob/9.x/src/Engines/MeiliSearchEngine.php
    private static function getSearchFilter($conditions)
    {
        $filters = collect($conditions)->map(function ($value, $key) {
            if (is_bool($value)) {
                return sprintf('%s=%s', $key, $value ? 'true' : 'false');
            }

            return is_numeric($value)
                            ? sprintf('%s=%s', $key, $value)
                            : sprintf('%s="%s"', $key, $value);
        });

        return $filters->values()->implode(' AND ');
    }

    public static function getSearchDocument(PostVariant $postVariant) : array
    {

        $post = $postVariant->post;
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
            'content' => $post->content ? PostContentRepository::getText($postVariant->content, $blog) : '',
            'slug' => $post->slug,

            /**
             * Reference and filtering
             */
            'blog_id' => $blog->id,
            'language_id' => $postVariant->language_id,
            'is_published' => $postVariant->status === 'published',    // search only needs to know if the post is published 
                                                                // (data API vs console API search)
            'is_page' => $post->is_page

        ];

    }

    /**
     * Updates filterable attributes
     * https://docs.meilisearch.com/learn/advanced/filtering_and_faceted_search.html
     * 
     * This should be called when attributes change
     */
    public static function setFilterableAttributes() : void
    {
        if (self::isMeilisearch()) {
            self::getIndex()->updateFilterableAttributes(self::FILTERABLE_ATTRIBUTES);
        }
    }


    public static function setSearchableAttributes() : void
    {
        if (self::isMeilisearch()) {
            self::getIndex()->updateSearchableAttributes(self::SEARCHABLE_ATTRIBUTES);
        }
    }

    private static function getIndex()
    {

        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
        return $client->index(self::SEARCH_INDEX_NAME);

    }

    private static function isMeilisearch()
    {
        return config('scout.driver') === 'meilisearch';
    }

}
