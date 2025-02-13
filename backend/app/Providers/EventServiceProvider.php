<?php declare(strict_types=1);

namespace App\Providers;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Events\BlogUrlChangedEvent;
use App\Domains\Blog\Listeners\CheckBlogUrlChangedListener;
use App\Domains\Blog\Listeners\UpdateContentHtmlOfAllPostsListener;
use App\Domains\Blog\Listeners\UpdateUrlsListener;
use App\Domains\Cache\Listeners\ClearCacheSubscriber;
use App\Domains\Integrations\HyvorTalk\HyvorTalkSubscriber;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\Listeners\PostVariantUpdateContentHtmlListener;
use App\Domains\Post\Listeners\PostVariantUpdateWordCountListener;
use App\Domains\PostHistory\Listeners\PostHistoryVariantUpdateListener;
use App\Domains\Shared\Count\CountSubscriber;
use App\Domains\Webhook\Listeners\WebhookSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [

        PostVariantUpdatedEvent::class => [
            PostVariantUpdateWordCountListener::class,
            PostVariantUpdateContentHtmlListener::class,
            PostHistoryVariantUpdateListener::class,
        ],

        BlogUpdatedEvent::class => [
            CheckBlogUrlChangedListener::class,
            UpdateContentHtmlOfAllPostsListener::class
        ],

        BlogUrlChangedEvent::class => [
            UpdateUrlsListener::class,
        ],

    ];

    /**
     * @var string[]
     */
    protected $subscribe = [

        ClearCacheSubscriber::class,
        CountSubscriber::class,
        WebhookSubscriber::class,

        // integrations
        HyvorTalkSubscriber::class,

    ];

    /*protected $observers = [

    ];*/

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }
}
