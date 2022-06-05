<?php
namespace Tests\Unit\Domains\Cache\Listeners;

use App\Domains\Cache\CacheRepository;
use App\Domains\Cache\Listeners\ClearTemplateCacheSubscriber;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('is attached', function () {

    Event::fake();

    // posts
    Event::assertListening(
        PostVariantUpdatedEvent::class,
        [ClearTemplateCacheSubscriber::class , 'onPostVariantUpdate']
    );

    // post variants
    Event::assertListening(
        PostVariantUpdatedEvent::class,
        [ClearTemplateCacheSubscriber::class , 'onPostVariantUpdate']
    );
    Event::assertListening(
        PostVariantDeletedEvent::class,
        [ClearTemplateCacheSubscriber::class, 'onPostVariantDelete']
    );

});

beforeEach(function() {

    $this->templateMock = function() {
        $this->mock(CacheRepository::class, function ($mock) {
            $mock
                ->shouldReceive('clearTemplateCache')
                ->once();
        })->makePartial();
    };

    $this->templateNoMock = function() {
        $this->mock(CacheRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('clearTemplateCache')
                ->never();
        })->makePartial();
    };

});

// POST ===

it('clears cache when editing a post', function() {

    ($this->templateMock)();

    $blog = blog();
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'published'
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->slug = 'new-slug';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostUpdate($event);

});

it('does not clear cache when editing a post if the primary variant is not published', function() {

    ($this->templateNoMock)();

    $blog = blog();
    $language = $blog->languages[0];

    $post = Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $language->id,
            'status' => 'draft'
        ]), 'variants')
        ->create(['blog_id' => $blog]);

    $post->slug = 'new-slug';

    $event = new PostUpdatedEvent($post);
    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostUpdate($event);

});


// POST VARIANTS ===

it('clears cache on post variant status change', function() {

    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $variant->status = 'published';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);

});

it('does not clear cache when attrs changes on non-published posts', function() {

    ($this->templateNoMock)();

    $variant = PostVariant::factory()->create(['status' => 'draft']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);

});

it('clears cache if the post is published', function() {

    ($this->templateMock)();

    $variant = PostVariant::factory()->create(['status' => 'published']);
    $variant->content = 'Hey';
    $event = new PostVariantUpdatedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantUpdate($event);

});

it('clears cache on post variant status delete', function() {

    ($this->templateMock)();

    $variant = PostVariant::factory()->create();
    $event = new PostVariantDeletedEvent($variant);

    $listener = new ClearTemplateCacheSubscriber();
    $listener->onPostVariantDelete($event);

});