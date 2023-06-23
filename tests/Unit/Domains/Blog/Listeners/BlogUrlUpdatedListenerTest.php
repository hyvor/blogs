<?php

namespace Tests\Unit\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Blog\Listeners\CheckBlogUrlChangedListener;
use Illuminate\Support\Facades\Event;

it('fires url updated event when changed', function() {

    Event::fake();
    Event::assertListening(BlogUpdatedEvent::class, CheckBlogUrlChangedListener::class);

    $blog = blog(['subdomain' => 'old-subdomain']);
    $blogOriginal = $blog->replicate();

    $blog->update([
        'subdomain' => 'new-subdomain'
    ]);

    $event = new BlogUpdatedEvent(
        $blog,
        $blogOriginal
    );

    (new CheckBlogUrlChangedListener)->handle($event);

    Event::assertDispatched(BlogUrlChangedEvent::class, function (BlogUrlChangedEvent $event) {

        expect($event->oldUrl)->toBe('https://old-subdomain.hyvorblogs.io');
        expect($event->newUrl)->toBe('https://new-subdomain.hyvorblogs.io');

        return true;
    });

});

it('fires on subdomain -> domain change', function() {

    Event::fake();

    $blog = blog(['subdomain' => 'old-subdomain']);
    $blogOriginal = $blog->replicate();

    $blog->update([
        'hosting_at' => 'domain',
        'hosting_domain' => 'myblog.com',
    ]);

    $event = new BlogUpdatedEvent(
        $blog,
        $blogOriginal
    );

    (new CheckBlogUrlChangedListener)->handle($event);

    Event::assertDispatched(BlogUrlChangedEvent::class, function (BlogUrlChangedEvent $event) {

        expect($event->oldUrl)->toBe('https://old-subdomain.hyvorblogs.io');
        expect($event->newUrl)->toBe('https://myblog.com');

        return true;
    });

});

it('does not fire when URL does not change', function() {

    Event::fake();

    $blog = blog();
    $blogOriginal = $blog->replicate();

    $blog->update([
        'theme_version_id' => 10
    ]);

    $event = new BlogUpdatedEvent(
        $blog,
        $blogOriginal
    );

    (new CheckBlogUrlChangedListener)->handle($event);

    Event::assertNotDispatched(BlogUrlChangedEvent::class);

});