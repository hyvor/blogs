<?php

namespace Tests\Unit\Domains\Integrations\HyvorTalk;

use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Integrations\HyvorTalk\HyvorTalkSubscriber;
use Illuminate\Support\Facades\Event;

it('updates domains', function() {
    Event::fake();
    Event::assertListening(BlogUrlChangedEvent::class, [HyvorTalkSubscriber::class, 'onBlogUrlUpdate']);
});