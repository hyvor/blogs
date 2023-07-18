<?php
 
namespace App\Domains\Post\Broadcast;
 
use App\Events\PostEditingUserChangedBroadcast;
use App\Domains\Post\Events\PostUpdatedEvent;
use Illuminate\Events\Dispatcher;
 
class PostEditingUserChangedSubscriber
{
 
    public static function onPostUpdate(PostUpdatedEvent $event): void
    {
        PostEditingUserChangedBroadcast::dispatch($event->post);
        if ($event->post->editing_user_id != $event->postOld->editing_user_id) {
            PostEditingUserChangedBroadcast::dispatch($event->post);
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(PostUpdatedEvent::class, [static::class, 'onPostUpdate']);
    }
}