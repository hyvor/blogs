<?php

namespace App\Service\Webhook;

use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\NavigationObject;
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
// TODO: RouteChangedEvent → ROUTES_CHANGED

class WebhookSubscriberListener
{
    public function __construct(
        private WebhookDeliveryService $deliveryService,
        private WebhookService $webhookService,
        private MessageBusInterface $bus,
        private NavigationService $navigationService,
        private LanguageService $languageService,
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
        $navigations = array_map(fn($nav) => (array)(new NavigationObject($nav)), $this->navigationService->getNavigations($blog));
        $this->call($blog, WebhookEvent::NAVIGATION_CHANGED, fn() => ['navigations' => $navigations]);
    }

    #[AsEventListener]
    public function onNavigationVariantChanged(NavigationVariantChangedEvent $event): void
    {
        $blog = $event->variant->getNavigation()->getBlog();
        $navigations = array_map(fn($nav) => (array)(new NavigationObject($nav)), $this->navigationService->getNavigations($blog));
        $this->call($blog, WebhookEvent::NAVIGATION_CHANGED, fn() => ['navigations' => $navigations]);
    }

    #[AsEventListener]
    public function onLanguageChanged(LanguageChangedEvent $event): void
    {
        $blog = $event->language->getBlog();
        $languages = array_map(fn($lang) => (array)(new LanguageObject($lang)), $this->languageService->getAllLanguages($blog));
        $this->call($blog, WebhookEvent::LANGUAGES_CHANGED, fn() => ['languages' => $languages]);
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
