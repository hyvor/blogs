<?php

namespace Tests\Unit\Domains\Cache\Listeners;

use App\Domains\Cache\CacheRepository;
use App\Domains\Cache\Listeners\ClearTemplateCacheSubscriber;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function () {
    Event::fake();

    // posts
    Event::assertListening(PostUpdatedEvent::class, [ClearTemplateCacheSubscriber::class , 'onPostUpdate']);
    Event::assertListening(PostDeletedEvent::class, [ClearTemplateCacheSubscriber::class , 'onPostDelete']);

    // post variants
    Event::assertListening(PostVariantUpdatedEvent::class, [ClearTemplateCacheSubscriber::class , 'onPostVariantUpdate']);
    Event::assertListening(PostVariantDeletedEvent::class, [ClearTemplateCacheSubscriber::class, 'onPostVariantDelete']);

    // user
    $userEventListener = [ClearTemplateCacheSubscriber::class , 'onUserEvent'];
    $userVariantEventListener = [ClearTemplateCacheSubscriber::class , 'onUserVariantEvent'];
    Event::assertListening(UserCreatedEvent::class, $userEventListener);
    Event::assertListening(UserUpdatedEvent::class, $userEventListener);
    Event::assertListening(UserDeletedEvent::class, $userEventListener);
    Event::assertListening(UserVariantUpdatedEvent::class, $userVariantEventListener);
    Event::assertListening(UserVariantDeletedEvent::class, $userVariantEventListener);

    // tag
    $tagEventListener = [ClearTemplateCacheSubscriber::class , 'onTagEvent'];
    $tagVariantEventListener = [ClearTemplateCacheSubscriber::class , 'onTagVariantEvent'];
    Event::assertListening(TagCreatedEvent::class, $tagEventListener);
    Event::assertListening(TagUpdatedEvent::class, $tagEventListener);
    Event::assertListening(TagDeletedEvent::class, $tagEventListener);
    Event::assertListening(TagVariantUpdatedEvent::class, $tagVariantEventListener);
    Event::assertListening(TagVariantDeletedEvent::class, $tagVariantEventListener);
});

beforeEach(function () {
    $this->templateMock = function () {
        $this->mock(CacheRepository::class, function ($mock) {
            $mock
                ->shouldReceive('clearTemplateCache')
                ->once();
        })->makePartial();
    };

    $this->templateNoMock = function () {
        $this->mock(CacheRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('clearTemplateCache')
                ->never();
        })->makePartial();
    };
});

// POST ===

it('clears cache when editing a post', function () {
    ($this->templateMock)();

    $blog = blog();
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'published',
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->slug = 'new-slug';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostUpdate($event);
});

it('does not clear cache when editing a post if the primary variant is not published', function () {
    ($this->templateNoMock)();

    $blog = blog();
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'draft',
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->slug = 'new-slug';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostUpdate($event);
});

it('clears cache when a post is deleted', function () {
    ($this->templateMock)();

    $blog = blog();

    $post = Post::factory()->create(['blog_id' => $blog]);

    $post->slug = 'new-slug';

    $event = new PostDeletedEvent($post);
    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostDelete($event);
});

// POST VARIANTS ===

it('clears cache on post variant status change', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $variant->status = 'published';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('does not clear cache when attrs changes on non-published posts', function () {
    ($this->templateNoMock)();

    $variant = PostVariant::factory()->create(['status' => 'draft']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('clears cache if the post is published', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create(['status' => 'published']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);
});

it('clears cache on post variant status delete', function () {
    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $event = new PostVariantDeletedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantDelete($event);
});
