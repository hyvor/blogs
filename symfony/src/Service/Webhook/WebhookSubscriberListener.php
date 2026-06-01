<?php

namespace App\Service\Webhook;

use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\RouteObject;
use App\Entity\Blog;
use App\Entity\Enum\WebhookEvent;
use App\Message\WebhookDeliverMessage;
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
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

// TODO: BlogUpdatedEvent → BLOG_UPDATED
// TODO: BlogVariantUpdatedEvent → BLOG_UPDATED
// TODO: PostCreatedEvent → POST_CREATED
// TODO: PostUpdatedEvent, PostVariantUpdatedEvent, PostVariantCreatedEvent → POST_UPDATED
// TODO: PostDeletedEvent, PostVariantDeletedEvent → POST_DELETED / POST_UPDATED
// TODO: TagCreatedEvent → TAG_CREATED
// TODO: TagUpdatedEvent, TagVariantUpdatedEvent, TagVariantCreatedEvent → TAG_UPDATED
// TODO: TagDeletedEvent, TagVariantDeletedEvent → TAG_DELETED / TAG_UPDATED
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
    ) {}

    /**
     * @param array<string, mixed> $data
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
