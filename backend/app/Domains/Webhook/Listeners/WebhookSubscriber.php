<?php

namespace App\Domains\Webhook\Listeners;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Data\Objects\ConsoleAPI\Media\MediaObject;
use App\Data\Objects\ConsoleAPI\Navigation\NavigationObject;
use App\Data\Objects\ConsoleAPI\Post\PostObject;
use App\Data\Objects\ConsoleAPI\RouteObject;
use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\App\AppContext\AppContext;
use App\Domains\App\AppContext\AppContextType;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use App\Domains\Language\LanguageRepository;
use App\Domains\Navigation\NavigationRepository;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostVariantCreatedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Route\RouteRepository;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagVariantCreatedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserVariantCreatedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Domains\Route\Events\RouteChangedEvent;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\WebhookDeliveryService;
use App\Models\Blog;
use App\Data\Enums\WebhookEventEnum;
use App\Models\PostVariant;
use App\Models\TagVariant;
use App\Models\UserVariant;
use Illuminate\Events\Dispatcher;


class WebhookSubscriber
{
    public function subscribe(Dispatcher $events) : void
    {
        $events->listen(BlogUpdatedEvent::class, [static::class, 'onBlogUpdatedEvent']);
        $events->listen(BlogVariantUpdatedEvent::class, [static::class,'onBlogVariantUpdatedEvent']);

        $events->listen(PostCreatedEvent::class, [static::class,'onPostCreatedEvent']);
        $events->listen(PostVariantCreatedEvent::class, [static::class,'onPostVariantCreatedEvent']);
        $events->listen(PostUpdatedEvent::class, [static::class, 'onPostUpdatedEvent']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class,'onPostVariantUpdatedEvent']);
        $events->listen(PostDeletedEvent::class, [static::class,'onPostDeletedEvent']);
        $events->listen(PostVariantDeletedEvent::class, [static::class,'onPostVariantDeletedEvent']);

        $events->listen(TagCreatedEvent::class, [static::class,'onTagCreatedEvent']);
        $events->listen(TagVariantCreatedEvent::class, [static::class,'onTagVariantCreatedEvent']);
        $events->listen(TagUpdatedEvent::class, [static::class, 'onTagUpdatedEvent']);
        $events->listen(TagVariantUpdatedEvent::class, [static::class, 'onTagVariantUpdatedEvent']);
        $events->listen(TagDeletedEvent::class, [static::class, 'onTagDeletedEvent']);
        $events->listen(TagVariantDeletedEvent::class, [static::class, 'onTagVariantDeletedEvent']);

        $events->listen(UserCreatedEvent::class, [static::class,'onUserCreatedEvent']);
        $events->listen(UserVariantCreatedEvent::class, [static::class,'onUserVariantCreatedEvent']);
        $events->listen(UserUpdatedEvent::class, [static::class, 'onUserUpdatedEvent']);
        $events->listen(UserVariantUpdatedEvent::class, [static::class, 'onUserVariantUpdatedEvent']);
        $events->listen(UserDeletedEvent::class, [static::class, 'onUserDeletedEvent']);
        $events->listen(UserVariantDeletedEvent::class, [static::class, 'onUserVariantDeletedEvent']);

        $events->listen(MediaCreatedEvent::class, [static::class, 'onMediaCreatedEvent']);
        $events->listen(MediaDeletedEvent::class, [static::class, 'onMediaDeletedEvent']);

        $events->listen(NavigationChangedEvent::class, [static::class, 'onNavigationChangedEvent']);
        $events->listen(NavigationVariantChangedEvent::class, [static::class, 'onNavigationVariantChangedEvent']);
        $events->listen(RouteChangedEvent::class, [static::class, 'onRouteChangedEvent']);
        $events->listen(LanguageChangedEvent::class, [static::class, 'onLanguageChangedEvent']);

        $events->listen(CacheClearSingleEvent::class, [static::class, 'onCacheClearSingleEvent']);
        $events->listen(CacheClearTemplatesEvent::class, [static::class, 'onCacheClearTemplatesEvent']);
        $events->listen(CacheClearAllEvent::class, [static::class, 'onCacheClearAllEvent']);
    }

    private function isPrimaryLanguage(
        PostVariant | TagVariant | UserVariant $variant,
        Blog $blog): bool
    {
        return $variant->language_id === LanguageRepository::getPrimaryLanguage($blog)->id;
    }

    private function call(Blog $blog, WebhookEventEnum $eventName, callable $dataFunc) : void
    {
        if (AppContext::in(AppContextType::SEEDING_BLOG)) {
            return;
        }
        
        $webhooks = $blog->webhooks;
        foreach ($webhooks as $webhook) {
            if (in_array($eventName->value, $webhook->events)) {
                $data = $dataFunc();
                $delivery = WebhookDeliveryService::createDelivery($webhook, $eventName, $data);
                WebhookDeliveryJob::dispatch($delivery);
            }
        }
    }

    public function onBlogUpdatedEvent(BlogUpdatedEvent $event) : void
    {
        $blog = $event->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::BLOG_UPDATED, fn() => [
                'blog' => (array) new BlogObject($event->blog)
            ]);
        }
    }

    public function onBlogVariantUpdatedEvent(BlogVariantUpdatedEvent $event) : void
    {
        $blog = $event->variant->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::BLOG_UPDATED, fn() => [
                'blog' => (array) new BlogObject($blog) 
            ]);
        }
    }

    public function onPostCreatedEvent(PostCreatedEvent $event) : void
    {
        $blog = $event->post->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::POST_CREATED, fn() => [
                'post' => (array) new PostObject($event->post, $blog)
            ]);
        }
    }

    public function onPostVariantCreatedEvent(PostVariantCreatedEvent $event) : void
    {
        $post = $event->variant->post ?? null;
        $blog = $post  ? $post->blog : null;
        if ($post && $blog) {
            // checks if it is the post variant created event initaiated at the post created event
            if ($this->isPrimaryLanguage($event->variant, $blog))
                return;

            $this->call($blog, WebhookEventEnum::POST_UPDATED, fn() => [
                'post' => (array) new PostObject($post, $blog),
            ]);
        }
    }
 
    public function onPostUpdatedEvent(PostUpdatedEvent $event) : void
    {
        $blog = $event->post->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::POST_UPDATED, fn() => [
                'post' => (array) new PostObject($event->post, $blog),
            ]);
        }
    }

    public function onPostVariantUpdatedEvent(PostVariantUpdatedEvent $event) : void
    {
        $post = $event->variant->post ?? null;
        $blog = $post ? $post->blog : null;
        if ($post && $blog) {
            $this->call($blog, WebhookEventEnum::POST_UPDATED, fn() => [
                'post' => (array) new PostObject($post, $blog),
            ]);
        }
    }

    public function onPostDeletedEvent(PostDeletedEvent $event) : void
    {
        $blog = $event->post->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::POST_DELETED, fn() => [
                'post' => (array) new PostObject($event->post, $blog),
            ]);
        }
    }

    public function onPostVariantDeletedEvent(PostVariantDeletedEvent $event) : void
    {
        $post = $event->variant->post ?? null;
        $blog = $post ? $post->blog : null;
        if ($post && $blog) {
            $this->call($blog, WebhookEventEnum::POST_UPDATED, fn() => [
                'post' => (array) new PostObject($post, $blog),
            ]);
        }
    }

    public function onTagCreatedEvent(TagCreatedEvent $event) : void
    {
        $blog = $event->tag->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::TAG_CREATED, fn() => [
                'tag' => (array) new TagObject($event->tag, $blog),
            ]);
        }
    }

    public function onTagVariantCreatedEvent(TagVariantCreatedEvent $event) : void
    {
        $tag = $event->variant->tag ?? null;
        $blog = $tag ? $tag->blog : null;
        if ($tag && $blog) {
            // checks if it is the tag variant created event initaiated at the tag created event
            if ($this->isPrimaryLanguage($event->variant, $blog))
                return;

            $this->call($blog, WebhookEventEnum::TAG_UPDATED, fn() => [
                'tag' => (array) new TagObject($tag, $blog),
            ]);
        }
    }

    public function onTagUpdatedEvent(TagUpdatedEvent $event) : void
    {
        $blog = $event->tag->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::TAG_UPDATED, fn() => [
                'tag' => (array) new TagObject($event->tag, $blog),
            ]);
        }
    }

    public function onTagVariantUpdatedEvent(TagVariantUpdatedEvent $event) : void
    {
        $tag = $event->variant->tag ?? null;
        $blog = $tag ? $tag->blog : null;
        if ($tag && $blog) {
            $this->call($blog, WebhookEventEnum::TAG_UPDATED, fn() => [
                'tag' => (array) new TagObject($tag, $blog),
            ]);
        }
    }

    public function onTagDeletedEvent(TagDeletedEvent $event) : void
    {
        $blog = $event->tag->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::TAG_DELETED, fn() => [
                'tag' => (array) new TagObject($event->tag, $blog),
            ]);
        }
    }

    public function onTagVariantDeletedEvent(TagVariantDeletedEvent $event) : void
    {
        $tag = $event->variant->tag ?? null;
        $blog = $tag ? $tag->blog : null;
        if ($tag && $blog) {
            $this->call($blog, WebhookEventEnum::TAG_UPDATED, fn() => [
                'tag' => (array) new TagObject($tag, $blog),
            ]);
        }
    }

    public function onUserCreatedEvent(UserCreatedEvent $event) : void
    {
        $blog = $event->user->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::USER_CREATED, fn() => [
                'user' => (array) new UserObject($event->user, $blog),
            ]);
        }
    }

    public function onUserVariantCreatedEvent(UserVariantCreatedEvent $event) : void
    {
        $user = $event->variant->user ?? null;
        $blog = $user ? $user->blog : null;
        if ($user && $blog) {
            // checks if it is the user variant created event initaiated at the user created event
            if ($this->isPrimaryLanguage($event->variant, $blog))
                return;

            $this->call($blog, WebhookEventEnum::USER_UPDATED, fn() => [
                'user' => (array) new UserObject($user, $blog),
            ]);
        }
    }

    public function onUserUpdatedEvent(UserUpdatedEvent $event) : void
    {
        $blog = $event->user->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::USER_UPDATED, fn() => [
                'user' => (array) new UserObject($event->user, $blog),
            ]);
        }
    }

    public function onUserVariantUpdatedEvent(UserVariantUpdatedEvent $event) : void
    {
        $user = $event->variant->user ?? null;
        $blog = $user ? $user->blog : null;
        if ($user && $blog) {
            $this->call($blog, WebhookEventEnum::USER_UPDATED, fn() => [
                'user' => (array) new UserObject($user, $blog),
            ]);
        }
    }

    public function onUserDeletedEvent(UserDeletedEvent $event) : void
    {
        $blog = $event->user->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::USER_DELETED, fn() => [
                'user' => (array) new UserObject($event->user, $blog),
            ]);
        }
    }

    public function onUserVariantDeletedEvent(UserVariantDeletedEvent $event) : void
    {
        $user = $event->variant->user ?? null;
        $blog = $user ? $user->blog : null;
        if ($user && $blog) {
            $this->call($blog, WebhookEventEnum::USER_UPDATED, fn() => [
                'user' => (array) new UserObject($user, $blog),
            ]);
        }
    }

    public function onMediaCreatedEvent(MediaCreatedEvent $event) : void
    {
        $blog = $event->media->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::MEDIA_CREATED, fn() => [
                'media' => (array) new MediaObject($event->media, $blog),
            ]);
        }
    }

    public function onMediaDeletedEvent(MediaDeletedEvent $event) : void
    {
        $blog = $event->media->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::MEDIA_DELETED, fn() => [
                'media' => (array) new MediaObject($event->media, $blog),
            ]);
        }
    }

    public function onNavigationChangedEvent(NavigationChangedEvent $event) : void
    {
        $blog = $event->navigation->blog ?? null;
        if ($blog) {
            $navigations = NavigationRepository::getNavigations($blog)->mapInto(NavigationObject::class);

            $this->call($blog, WebhookEventEnum::NAVIGATION_CHANGED, fn() => [
                'navigation' => $navigations,
            ]);
        }
    }

    public function onNavigationVariantChangedEvent(NavigationVariantChangedEvent $event) : void
    {
        $blog = $event->variant->navigation->blog ?? null;
        if ($blog) {
            $navigations = NavigationRepository::getNavigations($blog)->mapInto(NavigationObject::class);

            $this->call($blog, WebhookEventEnum::NAVIGATION_CHANGED, fn() => [
                'navigation' => $navigations,
            ]);
        }
    }

    public function onRouteChangedEvent(RouteChangedEvent $event) : void
    {
        $blog = $event->route->blog ?? null;
        if ($blog) {
            $routes = RouteRepository::getRoutes($blog)->mapInto(RouteObject::class);

            $this->call($blog, WebhookEventEnum::ROUTES_CHANGED, fn() => [
                'routes' => $routes,
            ]);
        }
    }

    public function onLanguageChangedEvent(LanguageChangedEvent $event) : void
    {
        $blog = $event->language->blog ?? null;
        if ($blog) {
            $languages = LanguageRepository::getAllLanguages($blog)->mapInto(LanguageObject::class);

            $this->call($blog, WebhookEventEnum::LANGUAGES_CHANGED, fn() => [
                'languages' => $languages,
            ]);
        }
    }

    public function onCacheClearSingleEvent(CacheClearSingleEvent $event) : void
    {
        $blog = $event->blog ?? null;
        if ($blog) {
            $this->call($blog, WebhookEventEnum::CACHE_SINGLE, fn() => [
                'path' => $event->path
            ]);
        }
    }

    public function onCacheClearTemplatesEvent(CacheClearTemplatesEvent $event) : void
    {
        $this->call($event->blog, WebhookEventEnum::CACHE_TEMPLATES, fn() => []);
    }

    public function onCacheClearAllEvent(CacheClearAllEvent $event) : void
    {
        $this->call($event->blog, WebhookEventEnum::CACHE_ALL, fn() => []);
    }
}
