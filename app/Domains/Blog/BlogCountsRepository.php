<?php

namespace App\Domains\Blog;

use App\Models\Language;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BlogCountsRepository
{
    /**
     * Returns counts for the post filtering
     *
     * All published count
     * By status
     * By author (15 max)
     * By tag (15 max)
     */
    public static function getPostsCounts(int $blogId)
    { 

        $status = [];
        Post::select('status', DB::raw('COUNT(id) as count'))
            ->groupBy('status')
            ->where('blog_id', $blogId)
            ->get()
            ->map(function ($row) use (&$status) {
                $status[$row->status] = $row->count;
            });

        $featuredCount = Post::where('blog_id', $blogId)
            ->where('is_featured', true)
            ->count();

        $authors = User::where('blog_id', $blogId)
            // ->orderBy('posts_count', 'desc')
            ->limit(15)
            // ->select('id', 'slug', 'posts_count')
            ->select('id', 'slug')
            ->get();


        $tags = Tag::where('blog_id', $blogId)
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->select('id', 'slug', 'posts_count')
            ->get();

        $languages = Language::where('blog_id', $blogId)
            ->orderBy('created_at', 'asc')
            ->select('id', 'code')
            ->get();

        return [
            'status' => [
                'draft' => $status['draft'] ?? 0,
                'published' => $status['published'] ?? 0,
                'scheduled' => $status['scheduled'] ?? 0,
                'featured' => $featuredCount
            ],
            'authors' => $authors,
            'tags' => $tags,
            'languages' => $languages
        ];
    }
}
