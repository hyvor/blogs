<?php

namespace App\Domains\Integrations\Shopify\Listeners;

use App\Domains\Blog\Events\BlogDeletedEvent;
use App\Domains\Integrations\Shopify\ShopifyService;
use Illuminate\Events\Dispatcher;

class ShopifySubscriber
{
    public function subscribe(Dispatcher $events) : void
    {
        $events->listen(BlogDeletedEvent::class, [static::class, 'onBlogDelete']);
    }

    public function onBlogDelete(BlogDeletedEvent $event) : void
    {

        /**
         * Delete shop data
         */
        $blog = $event->blog;
        $shop = ShopifyService::getShopByBlog($blog);

        if ($shop) {
            ShopifyService::deleteShop($shop);
        }
    }
}
