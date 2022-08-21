<?php

namespace Tests\Unit\Domains\Cache\Listeners\ClearCacheSubscriber;

use App\Domains\Cache\CacheService;
use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Theme\Events\AssetEditedEvent;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Models\Media;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function () {
    Event::fake();

    Event::assertListening(MediaCreatedEvent::class, [ClearCacheSubscriber::class, 'onMediaEvent']);
    Event::assertListening(MediaDeletedEvent::class, [ClearCacheSubscriber::class, 'onMediaEvent']);
    Event::assertListening(AssetEditedEvent::class, [ClearCacheSubscriber::class, 'onAssetEdit']);
    Event::assertListening(StylesEditedEvent::class, [ClearCacheSubscriber::class, 'onStylesEdit']);
});

it('clears cache when creating a media', function () {
    $media = Media::factory()->create(['blog_id' => blog()]);

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearSingleCache')
            ->once()
            ->with('/media/'.$media->name)
    )->makePartial();

    $event = new MediaCreatedEvent($media);
    $listener = new ClearCacheSubscriber();
    $listener->onMediaEvent($event);
});

it('clears cache when deleting media', function () {
    $media = Media::factory()->create(['blog_id' => blog()]);

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
    $mock
        ->shouldReceive('clearSingleCache')
        ->once()
        ->with('/media/'.$media->name)
    )->makePartial();

    $event = new MediaDeletedEvent($media);
    $listener = new ClearCacheSubscriber();
    $listener->onMediaEvent($event);
});

it('clears cache when asset updates', function () {
    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearSingleCache')
            ->once()
            ->with('/assets/script.js')
    )->makePartial();

    $event = new AssetEditedEvent(blog(), 'script.js');
    $listener = new ClearCacheSubscriber();
    $listener->onAssetEdit($event);
});

it('clears cache when styles updates', function () {
    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
    $mock
        ->shouldReceive('clearSingleCache')
        ->once()
        ->with('/styles.css')
    )->makePartial();

    $event = new StylesEditedEvent(blog());
    $listener = new ClearCacheSubscriber();
    $listener->onStylesEdit($event);
});
