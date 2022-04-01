<?php

namespace App\Providers;

use App\Domains\Cache\Listeners\ClearPostCacheListener;
use App\Domains\Post\Events\PostPublishedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Domains\Tag\Events\TagEvents;
use App\Domains\Tag\Events\UpdateTagEvents;

use App\Domains\Tag\Listeners\TagListeners;
use App\Domains\Tag\Listeners\UpdateTagListeners;

use App\Domains\Tag\Observers\TagObservers;
use App\Models\Tag;

use App\Domains\User\Events\UserEvents;
use App\Domains\User\Listeners\UserListeners;
use App\Domains\User\Observers\UserObservers;
use App\Models\User;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TagEvents::class => [
            TagListeners::class,
        ],
        // UpdateTagEvents::class => [
        //     UpdateTagListeners::class,
        // ],
        UserEvents::class => [
            UserListeners::class,
        ],
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
        parent::boot();
        Tag::observe(TagObservers::class);
        User::observe(UserObservers::class);

    }
}
