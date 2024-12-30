<?php

namespace Tests\Unit\Domains\Integrations\HyvorTalk;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Domains\Integrations\HyvorTalk\HyvorTalkSubscriber;
use App\Models\HyvorTalkWebsite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('updates domains', function() {
    Event::fake();
    Event::assertListening(BlogUrlChangedEvent::class, [HyvorTalkSubscriber::class, 'onBlogUrlUpdate']);
    Event::assertListening(GatedContentChangedEvent::class, [HyvorTalkSubscriber::class, 'onGatedContentChange']);
});

it('updates encryption key on gated content change - creates encryption key', function() {

    Http::fake([
        'talk.hyvor.com/*' => Http::sequence()
            ->push(['encryption_key' => null])
            ->push(['encryption_key' => 'encryption-key'])
    ]);

    $blog = blog();
    $event = new GatedContentChangedEvent($blog);

    $htWebsite = HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 12,
    ]);;

    $listener = new HyvorTalkSubscriber();
    $listener->onGatedContentChange($event);

    expect($htWebsite->refresh()->encryption_key)->toBe('encryption-key');

});