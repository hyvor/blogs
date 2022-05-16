<?php

namespace App\Domains\User\Listeners;

use App\Domains\User\Events\UserEvent;

class UserListener
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
    public function handle(UserEvent $event)
    {
        // Great all 03 of them are linked perfectly. ( Observers, Events, Listeners )

        $userInfo = $event->user;

        return $userInfo;
    }

    public function subscribe($events)
    {
    }
}
