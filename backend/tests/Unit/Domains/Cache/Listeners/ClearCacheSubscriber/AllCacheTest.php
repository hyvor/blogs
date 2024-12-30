<?php

namespace Tests\Unit\Domains\Cache\Listeners\ClearCacheSubscriber;

use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Cache\CacheService;
use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Domains\Theme\Events\AssetEditedEvent;
use App\Models\Redirect;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function () {
    Event::fake();
    Event::assertListening(BlogUpdatedEvent::class, [ClearCacheSubscriber::class, 'onBlogUpdate']);
});

it('clears all cache when blog hosting at is changed', function() {

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
            $mock
                ->shouldReceive('clearAllCache')
                ->once()
    )->makePartial();

    $blog = blog(['hosting_at' => BlogHostingAtEnum::SUBDOMAIN]);
    $blogOriginal = $blog->replicate();
    $blog->hosting_at  = BlogHostingAtEnum::DOMAIN;

    $event = new BlogUpdatedEvent($blog, $blogOriginal);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogUpdate($event);

});

it('clears all cache when blog hosting domain is changed', function() {

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearAllCache')
            ->once()
    )->makePartial();

    $blog = blog(['hosting_domain' => 'hyvor.com']);
    $blogOriginal = $blog->replicate();
    $blog->hosting_domain = 'hyvor.community';

    $event = new BlogUpdatedEvent($blog, $blogOriginal);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogUpdate($event);

});

it('clears all cache when blog hosting url is changed', function() {

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearAllCache')
            ->once()
    )->makePartial();

    $blog = blog(['hosting_url' => 'https://hyvor.com']);
    $blogOriginal = $blog->replicate();
    $blog->hosting_url = 'https://hyvor.community';

    $event = new BlogUpdatedEvent($blog, $blogOriginal);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogUpdate($event);

});

it('does not clear all when other data is updated', function() {

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearAllCache')
            ->never()
    )->makePartial();

    $blog = blog(['hosting_redirect_subdomain' => false]);
    $blogOriginal = $blog->replicate();
    $blog->hosting_redirect_subdomain = true;

    $event = new BlogUpdatedEvent($blog, $blogOriginal);
    $listener = new ClearCacheSubscriber();
    $listener->onBlogUpdate($event);

});

it('clears all cache on dynamic redirect', function() {

    $redirect = Redirect::factory()->create(['blog_id' => blog(), 'path' => '/from', 'dynamic' => true]);

    $this->mock(
        CacheService::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('clearAllCache')
            ->once()
    )->makePartial();

    $event = new RedirectChangedEvent($redirect);
    $listener = new ClearCacheSubscriber();
    $listener->onRedirectEvent($event);

});