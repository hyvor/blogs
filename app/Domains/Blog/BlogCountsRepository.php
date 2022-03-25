<?php

namespace App\Domains\Blog;

use App\Data\Enums\CountEnum;
use App\Domains\Count\CountRepository;
use App\Models\Blog;
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

        $postsCounts = CountRepository::getCounts(new Blog, [
            CountEnum::BLOG_POSTS,
            CountEnum::BLOG_POSTS_DRAFT,
            CountEnum::BLOG_POSTS_SCHEDULED,
            CountEnum::BLOG_POSTS_FEATURED
        ]);

        $featuredCount = Post::where('blog_id', $blogId)
            ->where('is_featured', true)
            ->count();

        $authors = User::where('blog_id', $blogId)
            ->select('users.id', 'users.slug')
            ->selectRaw(CountRepository::getSubQueryForCount(new User, CountEnum::USER_POSTS, 'posts_count'))
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        $tags = Tag::where('blog_id', $blogId)
            ->select('tags.id', 'tags.slug')
            ->selectRaw(CountRepository::getSubQueryForCount(new Tag, CountEnum::TAG_POSTS, 'posts_count'))
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        return [
            'status' => [
                'published' =>  $postsCounts[CountEnum::BLOG_POSTS->value],
                'draft' => $postsCounts[CountEnum::BLOG_POSTS_DRAFT->value],
                'scheduled' =>  $postsCounts[CountEnum::BLOG_POSTS_SCHEDULED->value],
                'featured' =>  $postsCounts[CountEnum::BLOG_POSTS->value],
            ],
            'authors' => $authors,
            'tags' => $tags,
        ];
    }
}
