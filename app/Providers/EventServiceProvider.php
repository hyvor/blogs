<?php

namespace App\Providers;

use App\Domains\Cache\Listeners\ClearPostCacheListener;
use App\Domains\Post\Events\PostPublishedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Domains\Tag\Events\TagEvent;
use App\Domains\Tag\Events\UpdateTagEvent;

use App\Domains\Tag\Listeners\TagListener;
use App\Domains\Tag\Listeners\UpdateTagListener;

use App\Domains\Tag\Observers\TagObserver;
use App\Models\Tag;

use App\Domains\User\Events\UserEvent;
use App\Domains\User\Listeners\UserListener;
use App\Domains\User\Observers\UserObserver;
use App\Models\User;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // TagEvent::class => [
        //     TagListener::class,
        // ],
        // UpdateTagEvent::class => [
        //     UpdateTagListener::class,
        // ],
        // UserEvent::class => [
        //     UserListener::class,
        // ],
    ];

    protected $subscribe = [

        ClearPostCacheListener::class

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // parent::boot();
        // Tag::observe(TagObserver::class);
        // User::observe(UserObserver::class);

    }
}
