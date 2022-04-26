<?php

namespace App\Domains\Post;

use App\Domains\Language\LanguageRepository;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Tag;
use Carbon\Carbon;
use Hyvor\FilterQ\Facades\FilterQ;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    public static function getPostById(int $postId) : ?Post
    {
        return Post::find($postId);
    }

    public static function getPostByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug) : ?Post
    {
        $post = Post::where('blog_id', $blogId);
        if ($id) {
            $post->where('id', $id);
        } else {
            $post->where('slug', $slug);
        }
        return $post->first();
    }

    public static function getPostByBlogIdAndSlug(int $blogId, string $slug) : ?Post
    {
        return Post::where('blog_id', $blogId)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get posts of a blog
     * with filters, limit, and offset
     * This is for the ConsoleAPI
     */
    public static function getPosts(
        Blog $blog,
        ?string $status,
        ?int $authorId,
        ?int $tagId,
        ?int $startTimestamp,
        ?int $endTimestamp,
        ?string $search,
        ?int $limit, 
        int $offset = 0
    ) : Collection
    {

        $language = LanguageRepository::getPrimaryLanguage($blog);

        return Post::where('posts.blog_id', $blog->id)
            ->join('post_variants', function($join) use ($language) {
                $join->on('post_variants.post_id', '=', 'posts.id');
                $join->where('post_variants.language_id', '=', $language->id);
            })
            ->where('posts.is_page', false)
            ->when($authorId, function ($query) use ($authorId) {
                $query->join('post_author', function ($join) use ($authorId) {
                    $join->on('post_author.post_id', '=', 'posts.id');
                    $join->where('post_author.author_id', '=', $authorId);
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
                            Carbon::createFromTimestamp($startTimestamp)->toDateTimeString(),
                            Carbon::createFromTimestamp($endTimestamp)->toDateTimeString()
                        ]);
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'featured') {
                    $query->where('is_featured', true);
                } else {
                    $query->where('posts.status', $status);
                }
            })
            ->when($search, function ($query) use ($search) {
                $query->where('posts.title', 'LIKE', "$search%");
            })
            // to prevent selecting post_variants data
            ->select('posts.*')
            ->orderByRaw("FIELD(post_variants.status, 'draft') DESC") // drafts first
            ->orderBy('posts.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

    }

    public static function getPages(int $blogId) {

        return Post::where('blog_id', $blogId)
            ->where('is_page', true)
            ->get();

    }


    /**
     * Getting posts with FilterQ
     * This is for the Data API
     * 
     * ALWAYS USE NAMED ARGUMENT WHEN USING THIS FUNCTION
     */
    public static function getPostsWithFilterQ(
        Blog $blog,
        Language $language,
        ?string $filter,
        int $limit,
        int $offset = 0,
        array $orderBys = [
            ['posts.published_at', 'DESC']
        ],
        bool $isPages = false
    ) : CollectionWithTotal {

        $builder = FilterQ::expression($filter)
            ->builder(Post::class)
            ->keys(function($keys) {

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
                    ->column('posts.slug')
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
                    ->join(function($query) {
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
                    ->join(function($query) {
                        $query->leftJoin('post_author', 'post_author.post_id', '=', 'posts.id')
                            ->leftJoin('users', 'users.id', '=', 'post_author.user_id');
                    });

            })
            ->addWhere();

        foreach ($orderBys as $orderBy) {
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        $posts = $builder
            ->join('post_variants', function($join) use ($language) {
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

    public static function createPost(int $blogId, bool $isPage)
    {

        /**
         * Because Laravel doesn't fetch database default values for other columns
         * you have to manually fetch the record again by ID to prevent
         * status being null
         *
         * #ref https://github.com/laravel/framework/issues/21449
         */

        $post = Post::create([
            'blog_id' => $blogId,
            'is_page' => $isPage
        ]);

        return Post::find($post->id);
    }

    public static function updatePost(int $postId, array $updates)
    {
        $post = Post::find($postId);
        // $postTag = PostTag::find($postId);


        if (array_key_exists('slug', $updates)) {
            $slug = $updates['slug'];
            if (
                $slug === null &&
                $post->status === 'published' || $post->status === 'scheduled'
            ) {
                // slug cannot be null for published|scheduled posts
                // so don't update
            } else {
                $post->slug = $updates['slug'];
            }
        }
        if (array_key_exists('is_featured', $updates)) {
            $post->is_featured = $updates['is_featured'];
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

        if (array_key_exists('tag', $updates)) {
            // $postTag->code_head = $updates['tag'];
            $post = Tag::create([
                'post_id' => $postId,
                'tag_id' => $updates['tag'],
            ]);
        }
        
        // if (array_key_exists('code_foot', $updates)) {
        //     $post->code_foot = $updates['code_foot'];
        // }
        /**
         * Dispatch events
         */
        // add to an observer
        /* if ($post->isDirty('status') || true) {
            if ($post->status === 'published') {
                PostPublishedEvent::dispatch($post);
            } else if ($post->status === 'draft') {
                // 
            }
        } */

        $post->save();

        return $post;
    }

    public static function createPostVariant(int $postId, int $languageId) : PostVariant
    {

        $variant = PostVariant::create([
            'post_id' => $postId,
            'language_id' => $languageId
        ]);

        return PostVariant::find($variant->id);

    }

    public static function updatePostVariant(int $postId, int $languageId, array $updates)
    {

        $variant = self::getPostVariantByPostIdAndLanguageId($postId, $languageId);

        if (!$variant) {
            return;
        }

        // status
        if (array_key_exists('status', $updates)) {
            $status = $updates['status'];
            $variant->status = $status;
        }

        // published_at
        if (
            array_key_exists('published_at', $updates) &&
            in_array($variant->status, ['published', 'scheduled'])
        ) {
            $variant->published_at = Carbon::createFromTimestamp($updates['published_at']);
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
            $variant->title = $updates['title'];
        }

        // description
        if (array_key_exists('description', $updates)) {
            $variant->description = $updates['description'];
        }

        // featured_image
        if (array_key_exists('featured_image', $updates)) {
            $variant->featured_image = $updates['featured_image'];
        }

        $variant->save();

        return $variant;

    }

    public static function getPostVariantByPostIdAndLanguageId(int $postId, int $languageId) : ?PostVariant
    {

        return PostVariant::where('language_id', $languageId)
            ->where('post_id', $postId)
            ->first();

    }

    public static function deletePostVariant(int $postId, int $languageId)
    {
        PostVariant::where('language_id', $languageId)
            ->where('post_id', $postId)
            ->delete();
    }


    public static function deletePost(int $postId)
    {
        Post::find($postId)->delete();
    }


    public static function getFirstTag(Post $post) {
        return $post->tags[0];
    }
    public static function getFirstAuthor(Post $post) {
        return $post->tags()->withPivot('order')
            ->orderBy('order', 'asc')
            ->first();
    }

}
