<?php

namespace App\Domains\Tag\Listeners;

use App\Domains\Tag\Events\TagEvent;

class TagListener
{
    /*
    *
    * Create the event listener.
    *
    * @return void
    */
    public function __construct()
    {
        //
    }

    /*
    *
    * Handle the event.
    *
    * @param  object  $event
    * @return void
    */
    public function handle(TagEvent $event)
    {
        // Great all 03 of them are linked perfectly. ( Observers, Events, Listeners )

        dd('Event Listener');
        $userInfo = $event->tag;

        return $userInfo;
    }

    public function subscribe($events)
    {
    }
}
