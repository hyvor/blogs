<?php

namespace Tests\Unit\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('listens', function() {

    Event::fake();

    Event::listen(CacheClearSingleEvent::class, [WebhookSubscriber::class, 'onCacheClearSingleEvent']);
    Event::listen(CacheClearTemplatesEvent::class, [WebhookSubscriber::class, 'onCacheClearTemplatesEvent']);
    Event::listen(CacheClearAllEvent::class, [WebhookSubscriber::class, 'onCacheClearAllEvent']);

});

it('does not call delivery job when webhooks are not registered', function() {

    Queue::fake();

    createWebhookFor('cache.templates'); // wrong event

    $event = new CacheClearSingleEvent(blog(), '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertNotPushed(WebhookDeliveryJob::class);

});

it('calls webhook delivery job on cache clear single event', function() {

    Queue::fake();

    createWebhookFor('cache.single');

    $event = new CacheClearSingleEvent(blog(), '/test');
    $listener = new WebhookSubscriber();
    $listener->onCacheClearSingleEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) =>
        $job->eventName === 'cache.single' && $job->data['path'] === '/test'
    );

});

it('calls webhook delivery job on cache clear templates event', function() {

    Queue::fake();

    createWebhookFor('cache.templates');

    $event = new CacheClearTemplatesEvent(blog());
    $listener = new WebhookSubscriber();
    $listener->onCacheClearTemplatesEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->eventName === 'cache.templates');

});

it('calls webhook delivery job on cache clear all event', function() {


    Queue::fake();

    createWebhookFor('cache.all');

    $event = new CacheClearAllEvent(blog());
    $listener = new WebhookSubscriber();
    $listener->onCacheClearAllEvent($event);

    Queue::assertPushed(fn (WebhookDeliveryJob $job) => $job->eventName === 'cache.all');

});