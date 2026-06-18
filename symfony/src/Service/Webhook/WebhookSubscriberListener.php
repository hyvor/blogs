<?php

namespace App\Service\Webhook;

use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\RouteObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Entity\Blog;
use App\Entity\Enum\WebhookEvent;
use App\Message\WebhookDeliverMessage;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Navigation\NavigationService;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Route\RouteService;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantCreatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

// TODO: PostCreatedEvent → POST_CREATED
// TODO: PostUpdatedEvent, PostVariantUpdatedEvent, PostVariantCreatedEvent → POST_UPDATED
// TODO: PostDeletedEvent, PostVariantDeletedEvent → POST_DELETED / POST_UPDATED
// TODO: UserCreatedEvent → USER_CREATED
// TODO: UserUpdatedEvent, UserVariantUpdatedEvent, UserVariantCreatedEvent → USER_UPDATED
// TODO: UserDeletedEvent, UserVariantDeletedEvent → USER_DELETED / USER_UPDATED
// TODO: MediaCreatedEvent → MEDIA_CREATED
// TODO: MediaDeletedEvent → MEDIA_DELETED

class WebhookSubscriberListener
{
    public function __construct(
        private WebhookDeliveryService $deliveryService,
        private WebhookService $webhookService,
        private MessageBusInterface $bus,
        private NavigationService $navigationService,
        private LanguageService $languageService,
        private RouteService $routeService,
        private BlogObjectFactory $blogObjectFactory,
        private TagObjectFactory $tagObjectFactory,
    ) {}

    /**
     * @param callable(): array<string, mixed> $dataFunc
     */
    private function call(Blog $blog, WebhookEvent $event, callable $dataFunc): void
    {
        $webhooks = $this->webhookService->getWebhooks($blog);
        foreach ($webhooks as $webhook) {
            if (in_array($event, $webhook->getEvents(), true)) {
                $delivery = $this->deliveryService->createDelivery($webhook, $event, $dataFunc());
                $this->bus->dispatch(new WebhookDeliverMessage($delivery->getId()));
            }
        }
    }

    #[AsEventListener]
    public function onNavigationChanged(NavigationChangedEvent $event): void
    {
        $blog = $event->navigation->getBlog();
        $this->call($blog, WebhookEvent::NAVIGATION_CHANGED, function () use ($blog) {
            return ['navigations' => array_map(
                fn($nav) => (array)(new NavigationObject($nav)),
                $this->navigationService->getNavigations($blog),
            )];
        });
    }

    #[AsEventListener]
    public function onNavigationVariantChanged(NavigationVariantChangedEvent $event): void
    {
        $blog = $event->variant->getNavigation()->getBlog();
        $this->call($blog, WebhookEvent::NAVIGATION_CHANGED, function () use ($blog) {
            return ['navigations' => array_map(
                fn($nav) => (array)(new NavigationObject($nav)),
                $this->navigationService->getNavigations($blog),
            )];
        });
    }

    #[AsEventListener]
    public function onLanguageChanged(LanguageChangedEvent $event): void
    {
        $blog = $event->language->getBlog();
        $this->call($blog, WebhookEvent::LANGUAGES_CHANGED, function () use ($blog) {
            return ['languages' => array_map(
                fn($lang) => (array)(new LanguageObject($lang)),
                $this->languageService->getAllLanguages($blog),
            )];
        });
    }

    #[AsEventListener]
    public function onRouteChanged(RouteChangedEvent $event): void
    {
        $blog = $event->route->getBlog();
        $this->call($blog, WebhookEvent::ROUTES_CHANGED, function () use ($blog) {
            return ['routes' => array_map(
                fn($route) => (array)(new RouteObject($route)),
                $this->routeService->getRoutes($blog),
            )];
        });
    }

    #[AsEventListener]
    public function onBlogUpdated(BlogUpdatedEvent $event): void
    {
        $blog = $event->blog;
        $this->call($blog, WebhookEvent::BLOG_UPDATED, fn() => [
            'blog' => (array) $this->blogObjectFactory->create($blog),
        ]);
    }

    #[AsEventListener]
    public function onBlogVariantUpdated(BlogVariantUpdatedEvent $event): void
    {
        $blog = $event->variant->getBlog();
        $this->call($blog, WebhookEvent::BLOG_UPDATED, fn() => [
            'blog' => (array) $this->blogObjectFactory->create($blog),
        ]);
    }

    #[AsEventListener]
    public function onTagCreated(TagCreatedEvent $event): void
    {
        $tag = $event->tag;
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_CREATED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onTagUpdated(TagUpdatedEvent $event): void
    {
        $tag = $event->tag;
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_UPDATED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onTagDeleted(TagDeletedEvent $event): void
    {
        $tag = $event->tag;
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_DELETED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onTagVariantCreated(TagVariantCreatedEvent $event): void
    {
        $tag = $event->variant->getTag();
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_UPDATED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onTagVariantUpdated(TagVariantUpdatedEvent $event): void
    {
        $tag = $event->variant->getTag();
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_UPDATED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onTagVariantDeleted(TagVariantDeletedEvent $event): void
    {
        $tag = $event->variant->getTag();
        $blog = $tag->getBlog();
        $this->call($blog, WebhookEvent::TAG_UPDATED, fn() => [
            'tag' => (array) $this->tagObjectFactory->create($tag, $blog),
        ]);
    }

    #[AsEventListener]
    public function onCacheClearAll(CacheClearAllEvent $event): void
    {
        $this->call($event->blog, WebhookEvent::CACHE_ALL, fn() => []);
    }

    #[AsEventListener]
    public function onCacheClearSingle(CacheClearSingleEvent $event): void
    {
        $this->call($event->blog, WebhookEvent::CACHE_SINGLE, fn() => ['path' => $event->path]);
    }

    #[AsEventListener]
    public function onCacheClearTemplates(CacheClearTemplatesEvent $event): void
    {
        $this->call($event->blog, WebhookEvent::CACHE_TEMPLATES, fn() => []);
    }
}
