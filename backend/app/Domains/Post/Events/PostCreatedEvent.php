<?php

namespace App\Domains\Post\Events;

use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
