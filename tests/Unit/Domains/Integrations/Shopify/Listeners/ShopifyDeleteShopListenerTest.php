<?php

namespace Tests\Unit\Domains\Integrations\Shopify\Listeners;

use App\Domains\Blog\Events\BlogDeletedEvent;
use App\Domains\Integrations\Shopify\Listeners\ShopifySubscriber;
use App\Models\ShopifyShop;
use Illuminate\Support\Facades\Event;

it('listens', function () {
    Event::fake();
    Event::assertListening(BlogDeletedEvent::class, [ShopifySubscriber::class, 'onBlogDelete']);
});

it('deletes the shop when the blog is deleted', function () {
    $blog = getShopifyEnabledBlog();

    $event = new BlogDeletedEvent($blog);
    (new ShopifySubscriber())->onBlogDelete($event);

    expect(ShopifyShop::where('blog_id', $blog->id)->first())->toBeNull();
});
