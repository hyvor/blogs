<?php

namespace Tests\Unit\Domains\Blog\Listeners;

use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Jobs\UpdateUrlsJob;
use App\Domains\Blog\Listeners\UpdateUrlsListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('listens', function() {
    Event::fake();
    Event::assertListening(BlogUpdatedEvent::class, UpdateUrlsListener::class);
});

it('calls the job when subdomain is changed', function() {
    Queue::fake();

    $blog = blog();
    $oldBlog = $blog->replicate();

    $blog->subdomain = 'new-subdomain';
    $blog->save();

    $event = new BlogUpdatedEvent($oldBlog, $blog);
    (new UpdateUrlsListener())->handle($event);

    Queue::assertPushed(UpdateUrlsJob::class);
});

it('calls the job when subdomain -> domain', function() {

    Queue::fake();

    $blog = blog();
    $oldBlog = $blog->replicate();

    $blog->hosting_at = BlogHostingAtEnum::DOMAIN;
    $blog->hosting_domain = 'hyvor.com';
    $blog->save();

    $event = new BlogUpdatedEvent($oldBlog, $blog);
    (new UpdateUrlsListener())->handle($event);

    Queue::assertPushed(UpdateUrlsJob::class);

});


it('does not call on other changes', function() {

    Queue::fake();

    $blog = blog();
    $oldBlog = $blog->replicate();

    $blog->theme_version_id = 20;
    $blog->save();

    $event = new BlogUpdatedEvent($oldBlog, $blog);
    (new UpdateUrlsListener())->handle($event);

    Queue::assertNothingPushed();

});