<?php

declare(strict_types=1);

namespace App\Domains\Post;

use App\Domains\Post\Content\PostContentService;
use Carbon\Carbon;

class PostRepository
{

    /**
     * @return CollectionWithTotal<Post>
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
    ): CollectionWithTotal {
        $language ??= LanguageRepository::getPrimaryLanguage($blog);

        $base = Post::where('posts.blog_id', $blog->id)
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
            );

        $total = $base->count();

        /** @var \Illuminate\Support\Collection<int, Post> $posts */
        $posts = $base->select('posts.*') // to prevent selecting post_variants data
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

        return new CollectionWithTotal($posts, $total);
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
//    public static function getPostsWithFilterQ(
//        Blog $blog,
//        Language $language,
//        ?string $filter,
//        int $limit,
//        int $offset = 0,
//        array $orderBys = [
//            ['posts.published_at', 'DESC'],
//        ],
//        bool $isPages = false
//    ): CollectionWithTotal {
//        $builder = (new FilterQ)->expression($filter)
//            ->builder(Post::class)
//            ->keys(function ($keys) {
//                $keys->add('id')
//                    ->column('posts.id')
//                    ->valueType('int');
//
//                $keys->add('published_at')
//                    ->column('posts.published_at')
//                    ->valueType('date');
//
//                $keys->add('created_at')
//                    ->column('posts.created_at')
//                    ->valueType('date');
//
//                $keys->add('updated_at')
//                    ->column('post_variants.updated_at')
//                    ->valueType('date');
//
//                $keys->add('is_featured')
//                    ->column('posts.is_featured')
//                    ->valueType('bool')
//                    ->operators('=,!=');
//
//                $keys->add('slug')
//                    ->column('post_variants.slug')
//                    ->valueType('string|int')
//                    ->operators('=,!=');
//
//                $keys->add('featured_image_url')
//                    ->column('posts.featured_image_url')
//                    ->valueType('null')
//                    ->operators('=,!=');
//
//                $keys->add('canonical_url')
//                    ->column('posts.canonical_url')
//                    ->valueType('null')
//                    ->operators('=,!=');
//
//                $keys->add('words')
//                    ->column('post_variants.words')
//                    ->valueType('int');
//
//                $keys->add('tag.id')
//                    ->column('post_tag.tag_id')
//                    ->valueType('int')
//                    ->join('post_tag', 'post_tag.post_id', '=', 'posts.id', 'left');
//
//                $keys->add('tag.slug')
//                    ->column('tags.slug')
//                    ->operators('=,!=')
//                    ->valueType('int|string')
//                    ->join(function ($query) {
//                        $query->leftJoin('post_tag', 'post_tag.post_id', '=', 'posts.id')
//                            ->join('tags', 'tags.id', '=', 'post_tag.tag_id');
//                    });
//
//                $keys->add('author.id')
//                    ->column('post_author.user_id')
//                    ->valueType('int')
//                    ->join('post_author', 'post_author.post_id', '=', 'posts.id', 'left');
//
//                $keys->add('author.slug')
//                    ->column('users.slug')
//                    ->operators('=,!=')
//                    ->valueType('int|string')
//                    ->join(function ($query) {
//                        $query->leftJoin('post_author', 'post_author.post_id', '=', 'posts.id')
//                            ->leftJoin('users', 'users.id', '=', 'post_author.user_id');
//                    });
//            })
//            ->addWhere();
//
//        foreach ($orderBys as $orderBy) {
//            $builder->orderBy($orderBy[0], $orderBy[1]);
//        }
//
//        /** @var \Illuminate\Support\Collection<int, Post> $posts */
//        $posts = $builder
//            ->join('post_variants', function ($join) use ($language) {
//                $join->on('post_variants.post_id', '=', 'posts.id');
//                $join->where('post_variants.language_id', '=', $language->id);
//            })
//            ->where('posts.blog_id', $blog->id)
//            ->where('post_variants.status', 'published')
//            ->where('posts.is_page', $isPages)
//            ->select('posts.*')
//            ->limit($limit)
//            ->offset($offset)
//            ->get();
//
//        $total = $builder->offset(0)->count();
//
//        return new CollectionWithTotal($posts, $total);
//    }

    public static function getPostVariantByPostIdAndLanguageId(int $postId, int $languageId): ?PostVariant
    {
        return PostVariant::where('language_id', $languageId)
            ->where('post_id', $postId)
            ->first();
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
}
