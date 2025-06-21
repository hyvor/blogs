<?php

namespace Tests\Unit\Domains\Integrations\HyvorTalk;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Domains\Integrations\HyvorTalk\HyvorTalkSubscriber;
use App\Models\HyvorTalkWebsite;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

it('updates domains', function () {
    Event::fake();
    Event::assertListening(BlogUrlChangedEvent::class, [HyvorTalkSubscriber::class, 'onBlogUrlUpdate']);
    Event::assertListening(GatedContentChangedEvent::class, [HyvorTalkSubscriber::class, 'onGatedContentChange']);
});

it('updates encryption key on gated content change - creates encryption key', function () {
    $mockHttpClient = new MockHttpClient([
        new JsonMockResponse(['encryption_key' => null]),
        new JsonMockResponse(['encryption_key' => 'encryption-key'])
    ]);
    $this->app->bind(HttpClientInterface::class, fn() => $mockHttpClient);

    $blog = blog();
    $event = new GatedContentChangedEvent($blog);

    $htWebsite = HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 12,
    ]);;

    $listener = app(HyvorTalkSubscriber::class);
    $listener->onGatedContentChange($event);

    expect($htWebsite->refresh()->encryption_key)->toBe('encryption-key');
});
