<?php

namespace App\Domains\Shared\Count;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BlogPostsCountsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }

    public function handle()
    {
        $language = LanguageRepository::getPrimaryLanguage($this->blog);

        $statusCounts = PostVariant::where('language_id', $language->id)
            ->join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            ->where('posts.is_page', false)
            ->selectRaw('post_variants.status, COUNT(post_variants.id) as count')
            ->groupBy('post_variants.status')
            ->get();

        $published = $statusCounts->firstWhere('status', PostStatusEnum::PUBLISHED)->count ?? 0;
        $drafts = $statusCounts->firstWhere('status', PostStatusEnum::DRAFT)->count ?? 0;
        $scheduled = $statusCounts->firstWhere('status', PostStatusEnum::SCHEDULED)->count ?? 0;

        $featured = Post::where('blog_id', $this->blog->id)
            ->where('is_featured', true)
            ->count();

        $this->blog->setCounts([
            'posts' => $published,
            'posts_draft' => $drafts,
            'posts_scheduled' => $scheduled,
            'posts_featured' => $featured,
        ]);
    }

    public function uniqueId()
    {
        return $this->blog->id;
    }
}
