<?php declare(strict_types=1);

namespace App\Domains\Cache\Listeners;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use App\Domains\Cache\CacheService;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\PostRepository;
use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Domains\Route\Events\RouteChangedEvent;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\Theme\Events\AssetEditedEvent;
use App\Domains\Theme\Events\ConfigEditedEvent;
use App\Domains\Theme\Events\LangEditedEvent;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Domains\Theme\Events\TemplateEditedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class ClearCacheSubscriber
{
    private function clearTemplateCache(Blog $blog): void
    {
        $cache = app(CacheService::class);
        $cache->blog($blog)->clearTemplateCache();
    }

    private function clearSingleCache(Blog $blog, string $path): void
    {
        $cache = app(CacheService::class);
        $cache->blog($blog)->clearSingleCache($path);
    }

    public function subscribe(Dispatcher $events): void
    {
        $this->subscribeTemplateEvents($events);
        $this->subscribeSingleEvents($events);
        $this->subscribeAllEvents($events);
    }


    private function subscribeTemplateEvents(Dispatcher $events): void
    {
        $events->listen(BlogUpdatedEvent::class, [static::class, 'onBlogUpdate']);
        $events->listen(BlogVariantUpdatedEvent::class, [static::class, 'onBlogVariantUpdate']);

        $events->listen(PostUpdatedEvent::class, [static::class, 'onPostUpdate']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostDelete']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);
        $events->listen(PostVariantDeletedEvent::class, [static::class, 'onPostVariantDelete']);

        $events->listen(UserCreatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserUpdatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserDeletedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserVariantUpdatedEvent::class, [static::class, 'onUserVariantEvent']);
        $events->listen(UserVariantDeletedEvent::class, [static::class, 'onUserVariantEvent']);

        $events->listen(TagCreatedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagUpdatedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagDeletedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagVariantUpdatedEvent::class, [static::class, 'onTagVariantEvent']);
        $events->listen(TagVariantDeletedEvent::class, [static::class, 'onTagVariantEvent']);

        $events->listen(NavigationChangedEvent::class, [static::class, 'onNavigationEvent']);
        $events->listen(NavigationVariantChangedEvent::class, [static::class, 'onNavigationVariantEvent']);

        $events->listen(LanguageChangedEvent::class, [static::class, 'onLanguageEvent']);
        $events->listen(RouteChangedEvent::class, [static::class, 'onRouteEvent']);

        $events->listen(ConfigEditedEvent::class, [static::class, 'onFileEditedEvent']);
        $events->listen(TemplateEditedEvent::class, [static::class, 'onFileEditedEvent']);
        $events->listen(LangEditedEvent::class, [static::class, 'onFileEditedEvent']);

    }

    private function subscribeSingleEvents(Dispatcher $events): void
    {
        $events->listen(MediaCreatedEvent::class, [static::class, 'onMediaEvent']);
        $events->listen(MediaDeletedEvent::class, [static::class, 'onMediaEvent']);

        $events->listen(AssetEditedEvent::class, [static::class, 'onAssetEdit']);
        $events->listen(StylesEditedEvent::class, [static::class, 'onStylesEdit']);

        $events->listen(RedirectChangedEvent::class, [static::class, 'onRedirectEvent']);
    }

    private function subscribeAllEvents(Dispatcher $event): void
    {
    }


    public function onBlogUpdate(BlogUpdatedEvent $event) : void
    {
        $this->clearTemplateCache($event->blog);
    }

    public function onBlogVariantUpdate(BlogVariantUpdatedEvent $event) : void
    {
        $blog = $event->variant->blog;
        if ($blog) $this->clearTemplateCache($blog);
    }

    public function onPostUpdate(PostUpdatedEvent $event) : void
    {
        $post = $event->post;
        $blog = $post->blog;

        if (!$blog)
            return;

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);
        $primaryVariant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $primaryLanguage->id);

        if (!$primaryVariant)
            return;

        if ($primaryVariant->status !== PostStatusEnum::PUBLISHED) {
            return;
        }

        $this->clearTemplateCache($blog);
    }

    public function onPostDelete(PostDeletedEvent $event) : void
    {
        if ($blog = $event->post->blog) $this->clearTemplateCache($blog);
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event) : void
    {
        $variant = $event->variant;
        $post = $variant->post;
        if (!$post) return;

        $blog = $post->blog;
        if (!$blog) return;

        $variantOld = $event->variantOld;

        /**
         * Clear
         *  - if the status is changed
         *  - Variant status is published (which means data is changed)
         */
        if (
            $variantOld->status !== $variant->status ||
            $variant->status === PostStatusEnum::PUBLISHED
        ) {
            $this->clearTemplateCache($blog);
        }
    }

    public function onPostVariantDelete(PostVariantDeletedEvent $event) : void
    {
        $post = $event->variant->post;
        if (!$post) return;
        if ($blog = $post->blog) $this->clearTemplateCache($blog);
    }

    public function onUserEvent(
        UserCreatedEvent|UserUpdatedEvent|UserDeletedEvent $event
    ) : void
    {
        if ($blog = $event->user->blog) $this->clearTemplateCache($blog);
    }

    public function onUserVariantEvent(UserVariantUpdatedEvent|UserVariantDeletedEvent $event) : void
    {
        $user = $event->variant->user;
        if (!$user) return;
        if ($blog = $user->blog) $this->clearTemplateCache($blog);
    }

    public function onTagEvent(TagCreatedEvent|TagUpdatedEvent|TagDeletedEvent $event) : void
    {
        if ($blog = $event->tag->blog) $this->clearTemplateCache($blog);
    }

    public function onTagVariantEvent(TagVariantUpdatedEvent|TagVariantDeletedEvent $event) : void
    {
        $tag = $event->variant->tag;
        if (!$tag) return;
        if ($blog = $tag->blog) $this->clearTemplateCache($blog);
    }

    public function onNavigationEvent(NavigationChangedEvent $event) : void
    {
        if ($blog = $event->navigation->blog) $this->clearTemplateCache($blog);
    }
    public function onNavigationVariantEvent(NavigationVariantChangedEvent $event) : void
    {
        $nav = $event->variant->navigation;
        if (!$nav) return;
        if ($blog = $nav->blog) $this->clearTemplateCache($blog);
    }

    public function onLanguageEvent(LanguageChangedEvent $event) : void
    {
        if ($blog = $event->language->blog) $this->clearTemplateCache($blog);
    }

    public function onRouteEvent(RouteChangedEvent $event) : void
    {
        if ($blog = $event->route->blog) $this->clearTemplateCache($blog);
    }

    public function onFileEditedEvent(
        TemplateEditedEvent | ConfigEditedEvent | LangEditedEvent $event
    ) : void
    {
        $blog = $event->file->blog;
        if ($blog)
            $this->clearTemplateCache($blog);
    }

    public function onMediaEvent(MediaCreatedEvent|MediaDeletedEvent $event) : void
    {
        $media = $event->media;
        $blog = $media->blog;

        if (!$blog) return;

        $path = PermalinkRepository::getMediaPermalink($media, $blog, true);
        $this->clearSingleCache($blog, $path);
    }

    public function onAssetEdit(AssetEditedEvent $event) : void
    {
        $path = PermalinkRepository::getAssetPermalink($event->name, $event->blog, true);
        $this->clearSingleCache($event->blog, $path);
    }

    public function onStylesEdit(StylesEditedEvent $event) : void
    {
        $this->clearSingleCache($event->blog, '/styles.css');
    }

    public function onRedirectEvent(RedirectChangedEvent $event) : void
    {
        if ($blog = $event->redirect->blog)
            $this->clearSingleCache($blog, $event->redirect->path);
    }
}
