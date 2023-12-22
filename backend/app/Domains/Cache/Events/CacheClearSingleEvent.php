<?php

namespace App\Domains\Cache\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class CacheClearSingleEvent
{
    use Dispatchable;

    /**
     * A path
     */
    public string $path;

    /**
     * A blog
     */
    public Blog $blog;

    /**
     * $path = path to clear
     */
    public function __construct(Blog $blog, string $path)
    {
        $this->blog = $blog;
        $this->path = $path;
    }
}
