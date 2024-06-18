<?php

namespace Tests\Unit\Domains\Webhook\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostVariantCreatedEvent;
use App\Domains\Route\Events\RouteChangedEvent;
use App\Domains\Route\RouteRepository;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use App\Models\PostVariant;
use App\Models\Route;
use Database\Factories\LanguageFactory;
use Database\Factories\MediaFactory;
use Database\Factories\PostFactory;
use Database\Factories\PostVariantFactory;
use Database\Factories\RouteFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Data\Enums\WebhookEventEnum;

it('listens', function () {
    Event::fake();

    Event::assertListening(CacheClearSingleEvent::class, [WebhookSubscriber::class, 'onCacheClearSingleEvent']);
    Event::assertListening(CacheClearTemplatesEvent::class, [WebhookSubscriber::class, 'onCacheClearTemplatesEvent']);
    Event::assertListening(CacheClearAllEvent::class, [WebhookSubscriber::class, 'onCacheClearAllEvent']);
});

it('does not call delivery job when webhooks are not registered', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_TEMPLATES); // wrong event

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertNotPushed(WebhookDeliveryJob::class);
});

// it('calls webhook delivery job on blog updated event', function () {
//     Event::fake();

//     $blogOriginal = blog();
//     $blog = $blogOriginal;
//     $blog->subdomain = 'new-subdomain';

//     createWebhookFor($blog, WebhookEventEnum::BLOGS_UPDATED);

//     $event = new BlogUpdatedEvent($blog, $blogOriginal);
//     $listener = new WebhookSubscriber();
//     $listener->onBlogUpdatedEvent($event);

//     Queue::assertNotPushed(function (WebhookDeliveryJob $job) {
//         expect($job->delivery->event)->toBe(WebhookEventEnum::CACHE_TEMPLATES);
//         // expect($job->delivery->data['blog']['id'])->toBe($blogOriginal->id);
//         return true;
//     });
// });

it('calls webhook delivery job on post created event', function () {
    Queue::fake();

    $blog = blog();
    $post = PostFactory::new()->create([
        'blog_id' => $blog->id,
    ]);
    createWebhookFor($blog, WebhookEventEnum::POST_CREATED);

    $event = new PostCreatedEvent($post);
    $listener = new WebhookSubscriber();
    $listener->onPostCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
        expect($job->delivery->event)->toBe(WebhookEventEnum::POST_CREATED);
        expect($job->delivery->data['post']['id'])->toBe($post->id);
        return true;
    });
});

// it('calls webhook delivery job on post variant created event', function () {
//     Queue::fake();

//     $blog = blog();
//     $post = PostFactory::new()->create([
//         'blog_id' => $blog->id,
//     ]);
//     $postVariant = PostVariantFactory::new()->create([
//         'post_id' => $post->id,
//     ]);
//     RouteFactory::new()->create([
//         'blog_id' => $blog->id,
//         'name' => 'post',
//     ]);

//     createWebhookFor($blog, WebhookEventEnum::POST_UPDATED);

//     $event = new PostVariantCreatedEvent($postVariant);
//     $listener = new WebhookSubscriber();
//     $listener->onPostVariantCreatedEvent($event);

//     Queue::assertPushed(function (WebhookDeliveryJob $job) use ($post){
//         expect($job->delivery->event)->toBe(WebhookEventEnum::POST_UPDATED);
//         expect($job->delivery->data['post']['id'])->toBe($post->id);
//         return true;
//     });

// });

it('calls webhook delivery job on media created event', function () {
    Queue::fake();

    $blog = blog();
    $media = MediaFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::MEDIA_CREATED);

    $event = new MediaCreatedEvent($media);
    $listener = new WebhookSubscriber();
    $listener->onMediaCreatedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($media){
        expect($job->delivery->event)->toBe(WebhookEventEnum::MEDIA_CREATED);
        expect($job->delivery->data['media']['id'])->toBe($media->id);
        return true;
    });
});

it('calls webhook delivery job on media deleted event', function () {
    Queue::fake();
    $blog = blog();
    $media = MediaFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::MEDIA_DELETED);

    $event = new MediaDeletedEvent($media);
    $listener = new WebhookSubscriber();
    $listener->onMediaDeletedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($media){
        expect($job->delivery->event)->toBe(WebhookEventEnum::MEDIA_DELETED);
        expect($job->delivery->data['media']['id'])->toBe($media->id);
        return true;
    });
});

it('calls webhook delivery job on route change event', function () {
    Queue::fake();

    $blog = blog();
    $route = RouteFactory::new()->create([
        'blog_id' => $blog->id,
        'name' => 'post',
    ]);

    createWebhookFor($blog, WebhookEventEnum::ROUTES_CHANGED);

    $event = new RouteChangedEvent($route);
    $listener = new WebhookSubscriber();
    $listener->onRouteChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($route){
        expect($job->delivery->event)->toBe(WebhookEventEnum::ROUTES_CHANGED);
        expect($job->delivery->data['routes'][0]['id'])->toBe($route->id);
        return true;
    });
});

it('calls webhook delivery job on language changed event', function () {
    Queue::fake();

    $blog = blog();
    $language = LanguageFactory::new()->create([
        'blog_id' => $blog->id,
    ]);

    createWebhookFor($blog, WebhookEventEnum::LANGUAGES_CHANGED);

    $event = new LanguageChangedEvent($language);
    $listener = new WebhookSubscriber();
    $listener->onLanguageChangedEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) use ($language){
        expect($job->delivery->event)->toBe(WebhookEventEnum::LANGUAGES_CHANGED);
        expect($job->delivery->data['languages'][0]['id'])->toBe($language->id);
        return true;
    });
});

it('calls webhook delivery job on cache clear single event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_SINGLE);

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) {

        // convert above to expect
        expect($job->delivery->event)->toBe(WebhookEventEnum::CACHE_SINGLE);
        expect($job->delivery->data['path'])->toBe('/test');

        return true;
    });
});

it('calls webhook delivery job on cache clear templates event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_TEMPLATES);

    $event = new CacheClearTemplatesEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearTemplatesEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === WebhookEventEnum::CACHE_TEMPLATES);
});

it('calls webhook delivery job on cache clear all event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, WebhookEventEnum::CACHE_ALL);

    $event = new CacheClearAllEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearAllEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === WebhookEventEnum::CACHE_ALL);
});
