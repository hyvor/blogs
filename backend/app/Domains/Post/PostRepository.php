<?php

declare(strict_types=1);

namespace App\Domains\Post;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantCreatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Carbon\Carbon;
use DateTimeInterface;
use Hyvor\FilterQ\FilterQ;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostRepository
{

    public function __construct(
        private FullTextSearchService $fullTextSearchService
    ) {
    }

    public static function getPostById(int $postId): ?Post
    {
        return Post::find($postId);
    }

    public static function getPostByLanguageAndSlug(Language $language, string $slug): ?Post
    {
        $variant = PostVariant::where('language_id', $language->id)
            ->where('slug', $slug)
            ->first();

        if (!$variant) {
            return null;
        }

        return $variant->post;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(
        Blog $blog,
        ?string $status,
        ?int $authorId = null,
        ?int $tagId = null,
        ?int $startTimestamp = null,
        ?int $endTimestamp = null,
        ?string $search = null,
        int $limit = 10,
        int $offset = 0,
        ?Language $language = null,
    ): Collection {
        $language ??= LanguageRepository::getPrimaryLanguage($blog);

        return Post::where('posts.blog_id', $blog->id)
            ->join('post_variants', function ($join) use ($language) {
                $join->on('post_variants.post_id', '=', 'posts.id');
                $join->where('post_variants.language_id', '=', $language->id);
            })
            ->where('posts.is_page', false)
            ->when($authorId, function ($query) use ($authorId) {
                $query->join('post_author', function ($join) use ($authorId) {
                    $join->on('post_author.post_id', '=', 'posts.id');
                    $join->where('post_author.user_id', '=', $authorId);
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $query->join('post_tag', function ($join) use ($tagId) {
                    $join->on('post_tag.post_id', '=', 'posts.id');
                    $join->where('post_tag.tag_id', '=', $tagId);
                });
            })
            ->when($startTimestamp && $endTimestamp, function ($query) use ($startTimestamp, $endTimestamp) {
                $query
                    ->whereRaw(
                        '
                            COALESCE(posts.published_at, posts.created_at) > ? AND
                            COALESCE(posts.published_at, posts.created_at) < ?
                        ',
                        [
                            Carbon::createFromTimestamp((int)$startTimestamp)->toDateTimeString(),
                            Carbon::createFromTimestamp((int)$endTimestamp)->toDateTimeString(),
                        ]
                    );
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'featured') {
                    $query->where('posts.is_featured', true);
                } else {
                    $query->where('post_variants.status', $status);
                }
            })
            ->when(
                $search,
                function ($query, $search) {
                    $searchQuery = $this->fullTextSearchService->getSearchQuery($search);

                    $query->whereRaw("calculated_ts @@ to_tsquery(ts_language, ?)", [$searchQuery])
                        ->orderByRaw("ts_rank(calculated_ts, to_tsquery(ts_language, ?)) DESC", [$searchQuery]);
                },
                function ($query) {
                    $query->orderByRaw("CASE post_variants.status WHEN 'draft' THEN 1 ELSE 2 END")
                        ->orderBy('posts.published_at', 'desc')
                        ->orderBy('posts.created_at', 'desc');
                }
            )
            ->select('posts.*') // to prevent selecting post_variants data
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * @param Blog $blog
     * @return Collection<int, Post>
     */
    public static function getPages(Blog $blog): Collection
    {
        return Post::where('blog_id', $blog->id)
            ->where('is_page', true)
            ->get();
    }

    /**
     * Getting posts with FilterQ
     * This is for the Data API
     *
     * ALWAYS USE NAMED ARGUMENT WHEN USING THIS FUNCTION
     *
     * @param array<array<string>> $orderBys
     * @return CollectionWithTotal<Post>
     */
    public static function getPostsWithFilterQ(
        Blog $blog,
        Language $language,
        ?string $filter,
        int $limit,
        int $offset = 0,
        array $orderBys = [
            ['posts.published_at', 'DESC'],
        ],
        bool $isPages = false
    ): CollectionWithTotal {
        $builder = (new FilterQ)->expression($filter)
            ->builder(Post::class)
            ->keys(function ($keys) {
                $keys->add('id')
                    ->column('posts.id')
                    ->valueType('int');

                $keys->add('published_at')
                    ->column('posts.published_at')
                    ->valueType('date');

                $keys->add('created_at')
                    ->column('posts.created_at')
                    ->valueType('date');

                $keys->add('updated_at')
                    ->column('post_variants.updated_at')
                    ->valueType('date');

                $keys->add('is_featured')
                    ->column('posts.is_featured')
                    ->valueType('bool')
                    ->operators('=,!=');

                $keys->add('slug')
                    ->column('post_variants.slug')
                    ->valueType('string|int')
                    ->operators('=,!=');

                $keys->add('featured_image_url')
                    ->column('posts.featured_image_url')
                    ->valueType('null')
                    ->operators('=,!=');

                $keys->add('canonical_url')
                    ->column('posts.canonical_url')
                    ->valueType('null')
                    ->operators('=,!=');

                $keys->add('words')
                    ->column('post_variants.words')
                    ->valueType('int');

                $keys->add('tag.id')
                    ->column('post_tag.tag_id')
                    ->valueType('int')
                    ->join('post_tag', 'post_tag.post_id', '=', 'posts.id', 'left');

                $keys->add('tag.slug')
                    ->column('tags.slug')
                    ->operators('=,!=')
                    ->valueType('int|string')
                    ->join(function ($query) {
                        $query->leftJoin('post_tag', 'post_tag.post_id', '=', 'posts.id')
                            ->join('tags', 'tags.id', '=', 'post_tag.tag_id');
                    });

                $keys->add('author.id')
                    ->column('post_author.user_id')
                    ->valueType('int')
                    ->join('post_author', 'post_author.post_id', '=', 'posts.id', 'left');

                $keys->add('author.slug')
                    ->column('users.slug')
                    ->operators('=,!=')
                    ->valueType('int|string')
                    ->join(function ($query) {
                        $query->leftJoin('post_author', 'post_author.post_id', '=', 'posts.id')
                            ->leftJoin('users', 'users.id', '=', 'post_author.user_id');
                    });
            })
            ->addWhere();

        foreach ($orderBys as $orderBy) {
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        /** @var \Illuminate\Support\Collection<int, Post> $posts */
        $posts = $builder
            ->join('post_variants', function ($join) use ($language) {
                $join->on('post_variants.post_id', '=', 'posts.id');
                $join->where('post_variants.language_id', '=', $language->id);
            })
            ->where('posts.blog_id', $blog->id)
            ->where('post_variants.status', 'published')
            ->where('posts.is_page', $isPages)
            ->select('posts.*')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = $builder->offset(0)->count();

        return new CollectionWithTotal($posts, $total);
    }

    /**
     * @param array{
     *     published_at?: DateTimeInterface,
     *     featured_image_url?: ?string,
     *     is_page?: bool,
     *     is_featured?: bool,
     * } $attrs
     */
    public static function createPost(Blog $blog, array $attrs = []): Post
    {
        // create post
        $post = Post::create(array_merge([
            'blog_id' => $blog->id
        ], $attrs));

        // create post variant (primary language)
        self::createPostVariant($post, LanguageRepository::getPrimaryLanguage($blog));

        PostCreatedEvent::dispatch($post);

        /**
         * Because Laravel doesn't fetch database default values for other columns
         * you have to manually fetch the record again by ID to prevent
         * status being null
         *
         * #ref https://github.com/laravel/framework/issues/21449
         */
        /** @var Post $post */
        $post = Post::find($post->id);

        return $post;
    }

    /**
     * @param array{
     *     published_at?: int,
     *     is_featured?: bool,
     *     featured_image_url?: ?string,
     *     canonical_url?: ?string,
     *     code_head?: ?string,
     *     code_foot?: ?string,
     * } $updates
     */
    public static function updatePost(Post $post, array $updates): Post
    {
        // published_at
        if (array_key_exists('published_at', $updates)) {
            $post->published_at = Carbon::createFromTimestamp($updates['published_at']);
        }
        if (array_key_exists('is_featured', $updates)) {
            $post->is_featured = $updates['is_featured'];
        }
        if (array_key_exists('featured_image_url', $updates)) {
            $post->featured_image_url = $updates['featured_image_url'];
        }
        if (array_key_exists('canonical_url', $updates)) {
            $post->canonical_url = $updates['canonical_url'];
        }
        if (array_key_exists('code_head', $updates)) {
            $post->code_head = $updates['code_head'];
        }
        if (array_key_exists('code_foot', $updates)) {
            $post->code_foot = $updates['code_foot'];
        }

        $post->save();

        PostUpdatedEvent::dispatch($post);

        return $post;
    }

    public static function deletePost(Post $post): void
    {
        $post->variants->map(fn($variant) => self::deletePostVariant($post, $variant->language_id));
        $post->delete();

        PostDeletedEvent::dispatch($post);
    }

    public static function createPostVariant(Post $post, Language $language): PostVariant
    {
        $fts = new FullTextSearchService();

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'language_id' => $language->id,
            'ts_language' => $fts->findClosestRegconfigByLanguageCode($language->code),
        ]);


        PostVariantCreatedEvent::dispatch($variant);


        /** @var PostVariant $variant */
        $variant = PostVariant::find($variant->id);

        return $variant;
    }

    /**
     * @param array{
     *     slug?: string|null,
     *     status?: PostStatusEnum,
     *     content?: string | null,
     *     content_unsaved?: string | null,
     *     title?: string | null,
     *     description?: string | null,
     *     seo_primary_keyword?: string | null,
     *     seo_secondary_keywords?: string[],
     *     link_analysis?: array<string, number>
     * } $updates
     */
    public static function updatePostVariant(
        PostVariant $variant,
        array $updates,
        bool $event = true,
    ): PostVariant {
        if (array_key_exists('slug', $updates)) {
            $variant->slug = $updates['slug'];
        }

        // status
        if (array_key_exists('status', $updates)) {
            $status = $updates['status'];
            $variant->status = $status;

            if ($status === PostStatusEnum::PUBLISHED) {
                $post = $variant->post;

                if ($post->published_at === null) {
                    $post->published_at = now();
                    $post->save();
                }

                // a slug is required if the post is published
                if ($variant->slug === null) {
                    $title = $variant->title ?? $updates['title'] ?? null;
                    $slug = $title ? Str::slug($title) : Str::random();

                    // if the slug is already taken, generate a random slug
                    if (self::getPostByLanguageAndSlug($variant->language, $slug)) {
                        $slug = Str::random();
                    }

                    $variant->slug = $slug;
                }
            }
        }

        // content
        if (array_key_exists('content', $updates)) {
            /**
             * content update means either
             *  - user is saving a draft post
             *  - user is "updating" a non-draft post
             */
            $variant->content = $updates['content'];
            $variant->content_unsaved = null;
        }

        // content_unsaved
        if (array_key_exists('content_unsaved', $updates)) {
            /**
             * content_unsaved means
             *  - user is saving a non-draft variant
             */
            $variant->content_unsaved = $updates['content_unsaved'];
        }

        // title
        if (array_key_exists('title', $updates)) {
            $variant->title = $updates['title'] ? mb_substr($updates['title'], 0, 255) : null;
        }

        // description
        if (array_key_exists('description', $updates)) {
            $variant->description = $updates['description'] ?
                mb_substr($updates['description'], 0, 350) :
                null;
        }

        // seo primary keyword
        if (array_key_exists('seo_primary_keyword', $updates)) {
            $variant->seo_primary_keyword = $updates['seo_primary_keyword'] ?
                mb_substr($updates['seo_primary_keyword'], 0, 255) :
                null;
        }

        // seo secondary keywords
        if (array_key_exists('seo_secondary_keywords', $updates)) {
            $variant->seo_secondary_keywords = $updates['seo_secondary_keywords'] ?
                array_slice($updates['seo_secondary_keywords'], 0, 10) :
                [];
        }

        // link analysis
        if (array_key_exists('link_analysis', $updates)) {
            $variant->link_analysis = $updates['link_analysis'];
        }

        $original = new PostVariant((array)$variant->getOriginal());
        $variant->save();

        if ($event) {
            PostVariantUpdatedEvent::dispatch($variant, $original);
        }

        return $variant;
    }

    public static function getPostVariantByPostIdAndLanguageId(int $postId, int $languageId): ?PostVariant
    {
        return PostVariant::where('language_id', $languageId)
            ->where('post_id', $postId)
            ->first();
    }

    public static function getPostVariantById(int $id): ?PostVariant
    {
        return PostVariant::find($id);
    }

    public static function deletePostVariant(Post $post, int $languageId): void
    {
        $variant = PostVariant::where('language_id', $languageId)
            ->where('post_id', $post->id)
            ->first();

        if (!$variant) {
            return;
        }

        $variant->delete();
        PostVariantDeletedEvent::dispatch($variant);
    }

    public static function updateVariantHtml(PostVariant $variant): void
    {
        if (!$variant->content) {
            return;
        }

        $post = $variant->post;

        $blog = $post->blog;
        if (!$blog) {
            return;
        }

        $html = PostContentService::getHtml($variant->content, $blog);
        $text = PostContentService::getText($variant->content, $blog);

        $variant->content_html = $html;
        $variant->content_text = $text;
        $variant->save();
    }

    public static function clonePost(Post $post): Post
    {
        $clone = self::createPost($post->blog, [
            'published_at' => null,
            'is_page' => $post->is_page,
            'is_featured' => false,
            'featured_image_url' => $post->featured_image_url,
            'canonical_url' => $post->canonical_url,
            'code_head' => $post->code_head,
            'code_foot' => $post->code_foot,
        ]);

        foreach ($post->variants as $index => $variant) {
            if ($variant->language != LanguageRepository::getPrimaryLanguage($post->blog)) {
                $cloneVariant = self::createPostVariant($clone, $variant->language);
            }
            else {
                $cloneVariant = $clone->variants[0]; // Default variant already created
            }
            $cloneVariant = self::updatePostVariant($cloneVariant, [
                'slug' => null, // slug will be generated automatically
                'status' => PostStatusEnum::DRAFT,
                'content' => $variant->content,
                'content_unsaved' => $variant->content_unsaved,
                'title' => $variant->title,
                'description' => $variant->description,
                'seo_primary_keyword' => $variant->seo_primary_keyword,
                'seo_secondary_keywords' => $variant->seo_secondary_keywords,
                'link_analysis' => $variant->link_analysis,
            ], false);
            self::updateVariantHtml($cloneVariant);
        }
        return $clone;
    }
}
