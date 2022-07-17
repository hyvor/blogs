<?php

namespace App\Domains\Shared;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Events\Dispatcher;

class CountSubscriber
{

    public function subscribe(Dispatcher $events)
    {
        $events->listen(PostCreatedEvent::class, [static::class, 'onPostCreate']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostDelete']);
    }

    public function onPostCreate(PostCreatedEvent $event)
    {
        $blog = $event->post->blog;
        $this->updateBlogPostCounts($blog);
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event)
    {
        if ($event->variant->status !== $event->variantOld->status) {
            $this->updateBlogPostCounts($event->variant->post->blog);
        }
    }

    protected function updateBlogPostCounts(Blog $blog)
    {

        dispatch(function() use ($blog) {

            $language = LanguageRepository::getPrimaryLanguage($blog);

            $statusCounts = PostVariant::where('language_id', $language->id)
                ->join('posts', 'posts.id', '=', 'post_variants.post_id')
                ->where('posts.blog_id', $blog->id)
                ->selectRaw('post_variants.status, COUNT(post_variants.id) as count')
                ->groupBy('post_variants.status')
                ->get();

            $published = $statusCounts->firstWhere('status', PostStatusEnum::PUBLISHED)->count ?? 0;
            $drafts = $statusCounts->firstWhere('status', PostStatusEnum::DRAFT)->count ?? 0;
            $scheduled = $statusCounts->firstWhere('status', PostStatusEnum::SCHEDULED)->count ?? 0;

            $featured = Post::where('blog_id', $blog->id)
                ->where('is_featured', true)
                ->count();

            $blog->setCounts([
                'posts' => $published,
                'posts_draft' => $drafts,
                'posts_scheduled' => $scheduled,
                'posts_featured' => $featured
            ]);

        });
    }

}