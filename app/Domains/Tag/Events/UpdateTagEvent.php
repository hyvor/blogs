<?php
namespace App\Domains\Tag\Events;

use App\Models\Tag;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateTagEvent
{

    use SerializesModels, InteractsWithSockets, Dispatchable;

    public Tag $tag;

    /**
    * Create a new event instance.
    *
    * @return void
    */
    public function __construct(Tag $tag)
    {
        // Great this data is coming from the tag observer.
        dd('Event');
        $this->tag = $tag; 
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