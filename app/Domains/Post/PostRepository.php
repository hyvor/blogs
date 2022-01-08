<?php
namespace App\Domains\Post;

use App\Models\Blog;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Types\Post\PostInputListFiltersType;
use Illuminate\Support\Facades\DB;

class PostRepository {

    static function postById(int $postId) {
        return Post::find($postId);
    }

    static function byBlogIdAndIdentifier(int $blogId, int $postid = null, string $slug = null) {
        
    }

    /**
     * Get posts of a blog
     * with filters, limit, and offset
     */
    static function getPosts(int $blogId, PostInputListFiltersType $filters, ?int $limit, int $offset = 0) {
        $status = $filters->status;
        $authorId = $filters->authorId;
        $tagId = $filters->tagId;
        $startTimestamp = $filters->startTimestamp;
        $endTimestamp = $filters->endTimestamp;
        $search = $filters->search;

        $limit = $limit ?? 50;

        return Post::where('blog_id', $blogId)
        ->when($authorId, function($query) use ($authorId) {
            $query->join('post_author', function($join) use ($authorId) {
                $join->on('post_author.post_id', '=', 'posts.id');
                $join->on('post_author.author_id', '=', $authorId);
            }); 
        })->when($tagId, function($query) use ($tagId) {
            $query->join('post_tag', function($join) use ($tagId) {
                $join->on('post_tag.post_id', '=', 'posts.id');
                $join->on('post_tag.tag_id', '=', $tagId);
            });
        })->when($startTimestamp && $endTimestamp, function($query) use ($startTimestamp, $endTimestamp) {
            $query->whereDate('created_at', '>', $startTimestamp)
                ->whereDate('created_at', '<', $endTimestamp);
        })
        // status
        ->when($status === 'all', function($query) {
            $query->where('posts.status', '!=', 'deleted');
        }, function ($query) use ($status) {
            $query->where('posts.status', $status);
        })
        ->when($search, function($query) use ($search) {
            $query->where('posts.title', 'LIKE', "$search%");
        })
        ->orderByRaw("FIELD(posts.status, 'draft') DESC") // drafts first
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();
    }

    static function createPost(int $blogId, bool $isPage) {

        /**
         * Because Laravel doesn't fetch database default values for other colums
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

    static function deletePost($postId) {
        Post::find($postId)->delete();
    }

}