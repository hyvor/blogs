<?php

namespace App\Tests\Service\Cache;

use App\Service\Cache\BlogCacheService;
use App\Service\Cache\ClearCacheListener;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\NavigationVariantFactory;
use App\Tests\Factory\RedirectFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ClearCacheListener::class)]
#[UsesClass(BlogCacheService::class)]
class ClearCacheListenerTest extends KernelTestCase
{
    private function dispatch(object $event): void
    {
        $this->getService(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }

    public function test_navigation_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $nav = NavigationFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new NavigationChangedEvent($nav));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_navigation_variant_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $nav = NavigationFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);
        $variant = NavigationVariantFactory::createOne([
            'navigation' => $nav,
            'language' => $lang,
            'language_id' => $lang->getId(),
        ]);

        $this->dispatch(new NavigationVariantChangedEvent($variant));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_language_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new LanguageChangedEvent($lang));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_redirect_changed_clears_single_cache_for_non_dynamic(): void
    {
        $blog = BlogFactory::createOne();
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-path',
            'dynamic' => false,
        ]);

        $this->dispatch(new RedirectChangedEvent($redirect));

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    public function test_redirect_changed_clears_all_cache_for_dynamic(): void
    {
        $blog = BlogFactory::createOne();
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/wildcard',
            'dynamic' => true,
        ]);

        $this->dispatch(new RedirectChangedEvent($redirect));

        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
        $this->getEd()->assertNotDispatched(CacheClearSingleEvent::class);
    }

    public function test_route_changed_clears_template_cache(): void
    {
        $blog = BlogFactory::createOne();
        $route = RouteFactory::createOne(['blog' => $blog, 'blog_id' => $blog->getId()]);

        $this->dispatch(new RouteChangedEvent($route));

        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }
}
