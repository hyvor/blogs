<?php

namespace Tests\Unit\Domains\Shared;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Shared\CountSubscriber;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;

it('is listening', function () {

    Event::fake();

    Event::assertListening(PostCreatedEvent::class, [CountSubscriber::class, 'onPostCreate']);
    Event::assertListening(PostVariantUpdatedEvent::class, [CountSubscriber::class, 'onPostVariantUpdate']);
    Event::assertListening(PostDeletedEvent::class, [CountSubscriber::class, 'onPostDelete']);

});

it('updates blog post counts', function() {

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    // drafts & featured
    Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'draft'
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
            'is_featured' => true
        ]);

    // scheduled
    Post::factory()
        ->count(3)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'scheduled'
        ]), 'variants')
        ->create([
            'blog_id' => $blog
        ]);

    Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published'
        ]), 'variants')
        ->create([
            'blog_id' => $blog
        ]);

    $listener = new CountSubscriber();
    invade($listener)->updateBlogPostCounts($blog);

    expect($blog->getCount('posts'))->toBe(1);
    expect($blog->getCount('posts_draft'))->toBe(2);
    expect($blog->getCount('posts_scheduled'))->toBe(3);
    expect($blog->getCount('posts_featured'))->toBe(2);

});

it('calls update blog counts on post creating', function() {

    $this->mock(CountSubscriber::class, function ($mock) {
        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('updateBlogPostCounts')
            ->once();
    })->makePartial();

    $post = Post::factory()->create([
        'blog_id' => blog()
    ]);

    $event = new PostCreatedEvent($post);
    $listener = app(CountSubscriber::class);
    $listener->onPostCreate($event);

});