<?php

namespace App\Domains\Post\Events;

use App\Models\Post;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public Post $post;
    public Post $post_old;

    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->post_old = $post->getOriginal();
    }
}