<?php

namespace Tests\Unit\Domains\Blog\Listeners;

use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Blog\Jobs\UpdateUrlsInBlogJob;
use App\Domains\Blog\Listeners\UpdateUrlsListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('listens', function() {
    Event::fake();
    Event::assertListening(BlogUrlChangedEvent::class, UpdateUrlsListener::class);
});

it('calls the job', function() {
    Queue::fake();

    $blog = blog(['subdomain' => 'old-subdomain']);
    $oldBlog = $blog->replicate();
    $blog->update([
        'subdomain' => 'new-subdomain'
    ]);

    $event = new BlogUrlChangedEvent(
        $blog,
        $oldBlog,
        'https://old-subdomain.hyvorblogs.io',
        'https://new-subdomain.hyvorblogs.io'
    );

    $listener = new UpdateUrlsListener();
    $listener->handle($event);

    Queue::assertPushed(UpdateUrlsInBlogJob::class);
});