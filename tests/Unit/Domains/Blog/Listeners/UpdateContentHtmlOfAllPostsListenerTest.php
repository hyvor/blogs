<?php

namespace Tests\Unit\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;
use App\Domains\Blog\Listeners\UpdateContentHtmlOfAllPostsListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('listens', function() {
    Event::fake();
    Event::assertListening(BlogUpdatedEvent::class, UpdateContentHtmlOfAllPostsListener::class);
});

it('calls the job if required data changes', function() {
    Queue::fake();

    $blog = blog();
    $oldBlog = $blog->replicate();

    $blog->setMeta('syntax_on', false);

    $event = new BlogUpdatedEvent($blog, $oldBlog);
    $listener = new UpdateContentHtmlOfAllPostsListener();
    $listener->handle($event);

    Queue::assertPushed(UpdateContentHtmlOfAllPostsJob::class);
});

it('does not call if other data changes', function() {

    Queue::fake();

    $blog = blog();
    $oldBlog = $blog->replicate();

    $blog->setMeta('embeddable', true);

    $event = new BlogUpdatedEvent($blog, $oldBlog);
    $listener = new UpdateContentHtmlOfAllPostsListener();
    $listener->handle($event);

    Queue::assertNothingPushed();

});