<?php
namespace Tests\Unit\Domains\Cache\Listeners\ClearCacheSubscriber;

use App\Domains\Cache\CacheService;
use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Models\Media;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function() {

    Event::fake();

    Event::assertListening(MediaCreatedEvent::class, [ClearCacheSubscriber::class, 'onMediaEvent']);
    Event::assertListening(MediaDeletedEvent::class, [ClearCacheSubscriber::class, 'onMediaEvent']);

});

it('clears cache when creating a media', function() {

    $media = Media::factory()->create(['blog_id' => blog()]);

    $this->mock(CacheService::class, fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearSingleCache')
            ->once()
            ->with('/media/'.$media->name)
    )->makePartial();

    $event = new MediaCreatedEvent($media);
    $listener = new ClearCacheSubscriber();
    $listener->onMediaEvent($event);

});

it('clears cache when deleting media', function() {

    $media = Media::factory()->create(['blog_id' => blog()]);

    $this->mock(CacheService::class, fn (MockInterface $mock) =>
    $mock
        ->shouldReceive('clearSingleCache')
        ->once()
        ->with('/media/'.$media->name)
    )->makePartial();

    $event = new MediaDeletedEvent($media);
    $listener = new ClearCacheSubscriber();
    $listener->onMediaEvent($event);

});