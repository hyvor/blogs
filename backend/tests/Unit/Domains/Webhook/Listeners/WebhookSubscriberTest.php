<?php

namespace Tests\Unit\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('listens', function () {
    Event::fake();

    Event::assertListening(CacheClearSingleEvent::class, [WebhookSubscriber::class, 'onCacheClearSingleEvent']);
    Event::assertListening(CacheClearTemplatesEvent::class, [WebhookSubscriber::class, 'onCacheClearTemplatesEvent']);
    Event::assertListening(CacheClearAllEvent::class, [WebhookSubscriber::class, 'onCacheClearAllEvent']);
});

it('does not call delivery job when webhooks are not registered', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, 'cache.templates'); // wrong event

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertNotPushed(WebhookDeliveryJob::class);
});

it('calls webhook delivery job on cache clear single event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, 'cache.single');

    $event = new CacheClearSingleEvent($blog, '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertPushed(function (WebhookDeliveryJob $job) {

        // convert above to expect
        expect($job->delivery->event)->toBe('cache.single');
        expect($job->delivery->data['path'])->toBe('/test');

        return true;
    });
});

it('calls webhook delivery job on cache clear templates event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, 'cache.templates');

    $event = new CacheClearTemplatesEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearTemplatesEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === 'cache.templates');
});

it('calls webhook delivery job on cache clear all event', function () {
    Queue::fake();

    $blog = blog();
    createWebhookFor($blog, 'cache.all');

    $event = new CacheClearAllEvent($blog);
    $listener = new WebhookSubscriber();
    $listener->onCacheClearAllEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->delivery->event === 'cache.all');
});
