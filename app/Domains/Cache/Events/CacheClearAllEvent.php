<?php

namespace App\Domains\Cache\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheClearAllEvent
{
    use Dispatchable;
    use SerializesModels;

    public Blog $blog;

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }
}
