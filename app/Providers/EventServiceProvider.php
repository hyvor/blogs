<?php

namespace App\Providers;

use App\Domains\Cache\Listeners\ClearPostCacheListener;
use App\Domains\Post\Events\PostPublishedEvent;
use App\Domains\Post\Observers\PostVariantObserver;
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

        ClearPostCacheListener::class

    ];

    protected $observers = [

        PostVariant::class => [PostVariantObserver::class]

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
