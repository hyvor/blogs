<?php

namespace App\Providers;

use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Integrations\Shopify\Listeners\ShopifySubscriber;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\Listeners\PostVariantUpdateContentHtmlListener;
use App\Domains\Post\Listeners\PostVariantUpdateWordCountListener;
use App\Domains\Shared\Count\CountSubscriber;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        PostVariantUpdatedEvent::class => [
            PostVariantUpdateWordCountListener::class,
            PostVariantUpdateContentHtmlListener::class,
        ],

    ];

    protected $subscribe = [

        ClearCacheSubscriber::class,
        CountSubscriber::class,
        WebhookSubscriber::class,

        // integrations
        ShopifySubscriber::class

    ];

    protected $observers = [

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }
}
