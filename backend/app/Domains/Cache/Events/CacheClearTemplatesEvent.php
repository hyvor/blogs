<?php

namespace App\Domains\Cache\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class CacheClearTemplatesEvent
{
    use Dispatchable;

    public Blog $blog;

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }
}
