<?php

namespace App\Providers;

use App\Domains\Cache\Listeners\ClearTemplateCacheSubscriber;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\Listeners\PostVariantUpdateContentHtmlListener;
use App\Domains\Post\Listeners\PostVariantUpdateWordCountListener;
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

        ClearTemplateCacheSubscriber::class,

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
