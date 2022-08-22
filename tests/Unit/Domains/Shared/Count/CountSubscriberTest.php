<?php

namespace Tests\Unit\Domains\Shared;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Shared\Count\BlogPostsCountsJob;
use App\Domains\Shared\Count\BlogUsersCountsJob;
use App\Domains\Shared\Count\CountSubscriber;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('is listening', function () {
    Event::fake();

    Event::assertListening(PostCreatedEvent::class, [CountSubscriber::class, 'onPostCreateOrDelete']);
    Event::assertListening(PostDeletedEvent::class, [CountSubscriber::class, 'onPostCreateOrDelete']);
    Event::assertListening(PostVariantUpdatedEvent::class, [CountSubscriber::class, 'onPostVariantUpdate']);

    Event::assertListening(UserCreatedEvent::class, [CountSubscriber::class, 'onUserEvent']);
    Event::assertListening(UserDeletedEvent::class, [CountSubscriber::class, 'onUserEvent']);
});

it('calls update blog counts on post creating', function () {
    Queue::fake();

    $post = Post::factory()->create([
        'blog_id' => blog(),
    ]);

    $event = new PostCreatedEvent($post);
    $listener = new CountSubscriber();
    $listener->onPostCreateOrDelete($event);

    Queue::assertPushed(fn (BlogPostsCountsJob $job) => $job->blog->id === blog()->id);
});

it('calls update when post variant status changes for primary variant', function () {
    Queue::fake();

    $post = aPublishedPost();
    $blog = blog();

    $variant = $post->variants[0];

    $variant->status = PostStatusEnum::DRAFT;

    $event = new PostVariantUpdatedEvent($variant);
    $listener = new CountSubscriber();
    $listener->onPostVariantUpdate($event);

    Queue::assertPushed(fn (BlogPostsCountsJob $job) => $job->blog->id === $blog->id);
});

it('does not call blog count update when other properties of variant is called', function () {
    Queue::fake();

    $post = aPublishedPost();
    $variant = $post->variants[0];

    $variant->title = 'Changed';

    $event = new PostVariantUpdatedEvent($variant);
    $listener = new CountSubscriber();
    $listener->onPostVariantUpdate($event);

    Queue::assertNothingPushed();
});

it('calls blog counts job on post delete', function () {
    Queue::fake();

    $post = Post::factory()->create([
        'blog_id' => blog(),
    ]);

    $event = new PostDeletedEvent($post);
    $listener = new CountSubscriber();
    $listener->onPostCreateOrDelete($event);

    Queue::assertPushed(fn (BlogPostsCountsJob $job) => $job->blog->id === blog()->id);
});

it('calls blog users counts job on user create', function() {
    Queue::fake();

    $user = User::factory()->create(['blog_id' => blog()]);
    $event = new UserCreatedEvent($user);
    $listener = new CountSubscriber();
    $listener->onUserEvent($event);

    Queue::assertPushed(fn (BlogUsersCountsJob $job) => $job->blog->id === blog()->id);

});


it('calls blog users counts job on user delete', function() {
    Queue::fake();

    $user = User::factory()->create(['blog_id' => blog()]);
    $event = new UserDeletedEvent($user);
    $listener = new CountSubscriber();
    $listener->onUserEvent($event);

    Queue::assertPushed(fn (BlogUsersCountsJob $job) => $job->blog->id === blog()->id);

});