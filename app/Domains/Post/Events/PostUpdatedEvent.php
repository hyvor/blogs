<?php

namespace App\Domains\Post\Events;

use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public Post $post;
    public Post $postOld;

    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->postOld = new Post($post->getOriginal());
    }
}