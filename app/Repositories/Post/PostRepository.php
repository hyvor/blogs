<?php
namespace App\Repositories\Post;

use App\Models\Blog;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Types\Post\PostInputListFiltersType;
use Illuminate\Support\Facades\DB;

class PostRepository {

    static function byId(int $postId) {

    }

    static function byBlogIdAndIdentifier(int $blogId, int $postid = null, string $slug = null) {
        
    }

    static function getPosts(Blog $blog, PostInputListFiltersType $filters, int $limit = 0, int $offset = 0) {
        $status = $filters->status;
        $authorId = $filters->authorId;
        $tagId = $filters->tagId;
        $startTimestamp = $filters->startTimestamp;
        $endTimestamp = $filters->endTimestamp;
        $search = $filters->search;

        Post::when($authorId, function($query) use ($authorId) {
            $query->join('post_author', function($join) use ($authorId) {
                $join->on('post_author.post_id', '=', 'posts.id');
                $join->on('post_author.author_id', '=', $authorId);
            }); 
        })->when($tagId, function($query) use ($tagId) {
            $query->join('post_tag', function($join) use ($tagId) {
                $join->on('post_tag.post_id', '=', 'posts.id');
                $join->on('post_tag.tag_id', '=', $tagId);
            });
        })->when($startTimestamp, function($query) use ($startTimestamp) {
            $query->whereDate('created_at', '>', $startTimestamp);
        })->when($endTimestamp, function($query) use ($endTimestamp) {
            $query->whereDate('created_at', '<', $endTimestamp);
        })
        // status
        ->when($status === 'all', function($query) {
            $query->where('posts.status', '!=', 'deleted');
        }, function ($query) use ($status) {
            $query->where('posts.status', $status);
        });
    }

}