<?php

namespace App\Service\Webhook;

use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\MediaObjectFactory;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Api\Console\Object\RouteObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObjectFactory;
use App\Entity\Blog;
use App\Entity\Enum\WebhookEvent;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use App\Service\Navigation\NavigationService;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\Post\Event\PostVariantUpdatedEvent;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Route\RouteService;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantCreatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\Event\UserVariantCreatedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\Event\UserVariantUpdatedEvent;
use App\Service\Webhook\Message\WebhookDeliverMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

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
        private MediaObjectFactory $mediaObjectFactory,
        private UserObjectFactory $userObjectFactory,
        private PostObjectFactory $postObjectFactory,
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
    public function onMediaCreated(MediaCreatedEvent $event): void
    {
        $media = $event->media;
        $blog = $media->getBlog();
        $this->call($blog, WebhookEvent::MEDIA_CREATED, fn() => [
            'media' => (array) $this->mediaObjectFactory->create($media, $blog),
        ]);
    }

    #[AsEventListener]
    public function onMediaDeleted(MediaDeletedEvent $event): void
    {
        $media = $event->media;
        $blog = $media->getBlog();
        $this->call($blog, WebhookEvent::MEDIA_DELETED, fn() => [
            'media' => (array) $this->mediaObjectFactory->create($media, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->user;
        $blog = $user->getBlog();
        $this->call($blog, WebhookEvent::USER_CREATED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserUpdated(UserUpdatedEvent $event): void
    {
        $user = $event->user;
        $blog = $user->getBlog();
        $this->call($blog, WebhookEvent::USER_UPDATED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserDeleted(UserDeletedEvent $event): void
    {
        $user = $event->user;
        $blog = $user->getBlog();
        $this->call($blog, WebhookEvent::USER_DELETED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserVariantCreated(UserVariantCreatedEvent $event): void
    {
        $user = $event->variant->getUser();
        $blog = $user->getBlog();

        // skip if this is the primary-language variant created as part of user creation
        if ($event->variant->getLanguage()->isPrimary()) {
            return;
        }

        $this->call($blog, WebhookEvent::USER_UPDATED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserVariantUpdated(UserVariantUpdatedEvent $event): void
    {
        $user = $event->variant->getUser();
        $blog = $user->getBlog();
        $this->call($blog, WebhookEvent::USER_UPDATED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onUserVariantDeleted(UserVariantDeletedEvent $event): void
    {
        $user = $event->variant->getUser();
        $blog = $user->getBlog();
        $this->call($blog, WebhookEvent::USER_UPDATED, fn() => [
            'user' => (array) $this->userObjectFactory->create($user, $blog),
        ]);
    }

    #[AsEventListener]
    public function onPostVariantUpdated(PostVariantUpdatedEvent $event): void
    {
        $post = $event->variant->getPost();
        $blog = $post->getBlog();
        $this->call($blog, WebhookEvent::POST_UPDATED, fn() => [
            'post' => (array) $this->postObjectFactory->create($post, $blog),
        ]);
    }

    #[AsEventListener]
    public function onPostVariantPublished(PostVariantPublishedEvent $event): void
    {
        $post = $event->variant->getPost();
        $blog = $post->getBlog();
        $this->call($blog, WebhookEvent::POST_VARIANT_PUBLISHED, fn() => [
            'post' => (array) $this->postObjectFactory->create($post, $blog),
        ]);
    }

    #[AsEventListener]
    public function onPostVariantUnpublished(PostVariantUnpublishedEvent $event): void
    {
        $post = $event->variant->getPost();
        $blog = $post->getBlog();
        $this->call($blog, WebhookEvent::POST_VARIANT_UNPUBLISHED, fn() => [
            'post' => (array) $this->postObjectFactory->create($post, $blog),
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
