<?php

namespace App\Domains\Tag\Events;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public Tag $tag;
    public Tag $tagOld;

    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
        $this->tagOld = new Tag($tag->getOriginal());
    }
}