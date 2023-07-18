<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostEditingUserChangedBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $blogId;
    public $user;
    public $postId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($blogId, $user, $postId)
    {
        $this->blogId = $blogId;
        $this->user = $user;
        $this->postId = $postId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("blog.{$this->blogId}");
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith()
    {
        return [
            'user' => $this->user,
            'postId' => $this->postId,
        ];
    }
}
