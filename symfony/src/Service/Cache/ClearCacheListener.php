<?php

namespace App\Service\Cache;

use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

// TODO: PostUpdatedEvent, PostDeletedEvent, PostVariantUpdatedEvent, PostVariantDeletedEvent → clearTemplateCache
// TODO: UserCreatedEvent, UserUpdatedEvent, UserDeletedEvent, UserVariantUpdatedEvent, UserVariantDeletedEvent → clearTemplateCache
// TODO: TemplateEditedEvent, ConfigEditedEvent, LangEditedEvent → clearTemplateCache
// TODO: MediaCreatedEvent, MediaDeletedEvent → clearSingleCache (with media permalink)
// TODO: AssetEditedEvent → clearSingleCache (with asset permalink)
// TODO: StylesEditedEvent → clearSingleCache('/styles.css') + clearTemplateCache
// TODO: GatedContentChangedEvent → clearTemplateCache


class ClearCacheListener
{
    public function __construct(private BlogCacheService $cacheService) {}

    #[AsEventListener]
    public function onNavigationChanged(NavigationChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->navigation->getBlog());
    }

    #[AsEventListener]
    public function onNavigationVariantChanged(NavigationVariantChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getNavigation()->getBlog());
    }

    #[AsEventListener]
    public function onLanguageChanged(LanguageChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->language->getBlog());
    }

    #[AsEventListener]
    public function onRouteChanged(RouteChangedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->route->getBlog());
    }

    #[AsEventListener]
    public function onBlogUpdated(BlogUpdatedEvent $event): void
    {
        $hostingChanged = $event->blogOld->getHostingAt() !== $event->blog->getHostingAt() ||
            $event->blogOld->getHostingDomain() !== $event->blog->getHostingDomain() ||
            $event->blogOld->getHostingUrl() !== $event->blog->getHostingUrl();

        if ($hostingChanged) {
            $this->cacheService->clearAllCache($event->blog);
        } else {
            $this->cacheService->clearTemplateCache($event->blog);
        }
    }

    #[AsEventListener]
    public function onBlogVariantUpdated(BlogVariantUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getBlog());
    }

    #[AsEventListener]
    public function onTagCreated(TagCreatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagUpdated(TagUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagDeleted(TagDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->tag->getBlog());
    }

    #[AsEventListener]
    public function onTagVariantUpdated(TagVariantUpdatedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getTag()->getBlog());
    }

    #[AsEventListener]
    public function onTagVariantDeleted(TagVariantDeletedEvent $event): void
    {
        $this->cacheService->clearTemplateCache($event->variant->getTag()->getBlog());
    }

    #[AsEventListener]
    public function onRedirectChanged(RedirectChangedEvent $event): void
    {
        $redirect = $event->redirect;
        $blog = $redirect->getBlog();

        if ($redirect->isDynamic()) {
            $this->cacheService->clearAllCache($blog);
        } else {
            $this->cacheService->clearSingleCache($blog, $redirect->getPath());
            // clear the old path cache as well if the redirect was updated
            if ($event->oldRedirect && $redirect->getPath() !== $event->oldRedirect->getPath()) {
                $this->cacheService->clearSingleCache($blog, $event->oldRedirect->getPath());
            }
        }
    }
}
