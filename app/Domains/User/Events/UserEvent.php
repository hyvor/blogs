<?php

namespace App\Domains\User\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent
{
    use SerializesModels;
    use InteractsWithSockets;
    use Dispatchable;

    public User $user;

    /**
    * Create a new event instance.
    *
    * @return void
    */
    public function __construct(User $user)
    {
        // Great this data is coming from the user observer.
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
