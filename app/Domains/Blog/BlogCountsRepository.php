<?php

namespace App\Domains\Blog;

use App\Data\Enums\CountEnum;
use App\Data\Objects\ConsoleAPI\Counts\AuthorCountObject;
use App\Data\Objects\ConsoleAPI\Counts\TagCountObject;
use App\Domains\Count\CountRepository;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

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
    public static function getPostsCounts(Blog $blog)
    {

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);

        $postsCounts = CountRepository::getCounts(new Blog(), [
            CountEnum::BLOG_POSTS,
            CountEnum::BLOG_POSTS_DRAFT,
            CountEnum::BLOG_POSTS_SCHEDULED,
            CountEnum::BLOG_POSTS_FEATURED,
        ]);

        $featuredCount = Post::where('blog_id', $blog->id)
            ->where('is_featured', true)
            ->count();

        $authors = User::where('blog_id', $blog->id)
            ->join('user_variants', function ($join) use ($primaryLanguage) {
                $join->on('user_variants.user_id', '=', 'users.id');
                $join->where('user_variants.language_id', '=', $primaryLanguage->id);
            })
            ->select('users.id', 'users.posts_count', 'user_variants.name')
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        $tags = Tag::where('blog_id', $blog->id)
            ->join('tag_variants', function ($join) use ($primaryLanguage) {
                $join->on('tag_variants.tag_id', '=', 'tags.id');
                $join->where('tag_variants.language_id', '=', $primaryLanguage->id);
            })
            ->select('tags.id', 'tags.posts_count', 'tag_variants.name')
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        return [
            'status' => [
                'published' => $postsCounts[CountEnum::BLOG_POSTS->value],
                'draft' => $postsCounts[CountEnum::BLOG_POSTS_DRAFT->value],
                'scheduled' => $postsCounts[CountEnum::BLOG_POSTS_SCHEDULED->value],
                'featured' => $postsCounts[CountEnum::BLOG_POSTS->value],
            ],
            'authors' => $authors->mapInto(AuthorCountObject::class),
            'tags' => $tags->mapInto(TagCountObject::class),
        ];
    }
}
