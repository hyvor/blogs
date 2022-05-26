<?php

namespace App\Providers;

use App\Domains\Blog\Observers\BlogObserver;
use App\Domains\Cache\Listeners\ClearPostCacheListener;
use App\Domains\Post\Observers\PostVariantObserver;
use App\Models\Blog;
use App\Models\PostVariant;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [



    ];

    protected $subscribe = [

        ClearPostCacheListener::class,

    ];

    protected $observers = [

        Blog::class => [BlogObserver::class],
        PostVariant::class => [PostVariantObserver::class],

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
