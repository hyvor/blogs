<?php

namespace App\Domains\Post;

use App\Data\Params\ConsoleAPI\PostsFilterParam;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Types\Post\PostInputListFiltersType;
use Carbon\Carbon;
use Hyvor\FilterQ\Facades\FilterQ;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get posts of a blog
     * with filters, limit, and offset
     * This is for the ConsoleAPI
     */
    public static function getPosts(
        int $blogId, PostsFilterParam $filters, ?int $limit, int $offset = 0
    ) : Collection
    {
        $status = $filters->status;
        $authorId = $filters->authorId;
        $tagId = $filters->tagId;
        $languageId = $filters->languageId;
        $startTimestamp = $filters->startTimestamp;
        $endTimestamp = $filters->endTimestamp;
        $search = $filters->search;

        $limit = $limit ?? 50;

        return Post::where('blog_id', $blogId)
        ->where('posts.is_page', false)
        ->when($authorId, function ($query) use ($authorId) {
            $query->join('post_author', function ($join) use ($authorId) {
                $join->on('post_author.post_id', '=', 'posts.id');
                $join->on('post_author.author_id', '=', $authorId);
            });
        })
        ->when($tagId, function ($query) use ($tagId) {
            $query->join('post_tag', function ($join) use ($tagId) {
                $join->on('post_tag.post_id', '=', 'posts.id');
                $join->on('post_tag.tag_id', '=', $tagId);
            });
        })
        ->when($languageId, function ($query) use ($languageId) {
            $query->where('posts.language_id', $languageId);
        })
        ->when($startTimestamp && $endTimestamp, function ($query) use ($startTimestamp, $endTimestamp) {
            $query->whereDate('created_at', '>', $startTimestamp)
                ->whereDate('created_at', '<', $endTimestamp);
        })
        // status
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
        ->orderByRaw("FIELD(posts.status, 'draft') DESC") // drafts first
        ->orderBy('created_at', 'desc')
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
     */
    public static function getPostsWithFilterQ(
        int $blogId, ?string $filterQExpression, 
        int $limit, int $offset,
        string $orderBy, string $orderMethod
    ) : Collection {

        $builder = FilterQ::expression($filterQExpression)
            ->builder(Post::class)
            ->keys(function($keys) {

                $keys->add('id')
                    ->column('posts.id')
                    ->operators('~', true);

                $keys->add('published_at')
                    ->column('posts.published_at')
                    ->operators('~', true);

                $keys->add('updated_at')
                    ->column('posts.updated_at')
                    ->operators('~', true);

                $keys->add('is_featured')
                    ->column('posts.is_featured')
                    ->operators('=,!=');

                $keys->add('slug')
                    ->column('posts.slug')
                    ->operators('=,!=,~');

                $keys->add('title')
                    ->column('posts.title')
                    ->operators('~');

                $keys->add('description')
                    ->column('posts.description')
                    ->operators('~,=,!=');

                $keys->add('featured_image')
                    ->column('posts.featured_image')
                    ->operators('=,!=');

                $keys->add('canonical_url')
                    ->column('posts.canonical_url')
                    ->operators('=,!=');

                $keys->add('reading_time')
                    ->column('posts.reading_time')
                    ->operators('~', true);

                $keys->add('tag.id')
                    ->column('post_tag.tag_id')
                    ->operators('=,!=')
                    ->join('post_tag', 'post_tag.post_id', '=', 'posts.id', 'left');

                $keys->add('tag.slug')
                    ->column('tags.slug')
                    ->operators('=,!=')
                    ->join(function($query) {
                        $query->leftJoin('post_tag', 'post_tag.post_id', '=', 'posts.id')
                            ->join('tags', 'tags.id', '=', 'post_tag.tag_id');
                    });

                $keys->add('author.id')
                    ->column('post_author.user_id')
                    ->operators('=,!=')
                    ->join('post_author', 'post_author.post_id', '=', 'posts.id', 'left');

                $keys->add('author.slug')
                    ->column('users.slug')
                    ->operators('=,!=')
                    ->join(function($query) {
                        $query->leftJoin('post_author', 'post_author.post_id', '=', 'posts.id')
                            ->leftJoin('users', 'users.id', '=', 'post_author.user_id');
                    });

            })
            ->operators(function($operators) {
                $operators->add('~', 'LIKE');
            })
            ->addWhere();

        return $builder
            ->with(['tags', 'authors'])
            ->where('posts.blog_id', $blogId)
            ->where('posts.status', 'published')
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderMethod)
            ->select('posts.*')
            ->get();

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

        if (
            array_key_exists('published_at', $updates) &&
            in_array($post->status, ['published', 'scheduled'])
        ) {
            $post->published_at = Carbon::createFromTimestamp($updates['published_at']);
        }
        if (array_key_exists('status', $updates)) {
            $post->status = $updates['status'];
        }
        if (array_key_exists('is_featured', $updates)) {
            $post->is_featured = $updates['is_featured'];
        }
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
        if (array_key_exists('content', $updates)) {
            /**
             * content update means either 
             *  - user is saving a draft post
             *  - user is "updating" a non-draft post
             */
            $post->content = $updates['content'];
            $post->content_unsaved = $updates['content_unsaved'];
        }
        if (array_key_exists('content_unsaved', $updates)) {
            /**
             * content_unsaved means
             *  - user is saving a non-draft post
             */
            $post->content_unsaved = $updates['content_unsaved'];
        }
        if (array_key_exists('title', $updates)) {
            $post->title = $updates['title'];
        }
        if (array_key_exists('description', $updates)) {
            $post->description = $updates['description'];
        }
        if (array_key_exists('featured_image', $updates)) {
            $post->featured_image = $updates['featured_image'];
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
        return $post;
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
