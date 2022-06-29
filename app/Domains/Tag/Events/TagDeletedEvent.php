<?php

namespace App\Domains\Tag\Events;

use App\Models\Tag;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagDeletedEvent
{
    use Dispatchable;
    use SerializesModels;

    public Tag $tag;

    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }
}
