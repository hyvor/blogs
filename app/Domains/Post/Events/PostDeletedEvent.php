<?php

namespace App\Domains\Post\Events;

use App\Models\Post;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostDeletedEvent
{
    use Dispatchable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}