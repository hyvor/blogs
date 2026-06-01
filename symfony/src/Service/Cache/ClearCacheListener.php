<?php

namespace App\Service\Cache;

use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

// TODO: BlogUpdatedEvent (hosting changed → clearAllCache, other → clearTemplateCache)
// TODO: BlogVariantUpdatedEvent → clearTemplateCache
// TODO: PostUpdatedEvent, PostDeletedEvent, PostVariantUpdatedEvent, PostVariantDeletedEvent → clearTemplateCache
// TODO: UserCreatedEvent, UserUpdatedEvent, UserDeletedEvent, UserVariantUpdatedEvent, UserVariantDeletedEvent → clearTemplateCache
// TODO: TagCreatedEvent, TagUpdatedEvent, TagDeletedEvent, TagVariantUpdatedEvent, TagVariantDeletedEvent → clearTemplateCache
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
