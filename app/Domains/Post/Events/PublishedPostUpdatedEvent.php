<?php
namespace App\Domains\Post\Events;

use App\Models\Post;
use Illuminate\Queue\SerializesModels;

class PublishedPostUpdatedEvent 
{

    use SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {

        $this->post = $post;

    }

}