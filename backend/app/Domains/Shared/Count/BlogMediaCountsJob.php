<?php

namespace App\Domains\Shared\Count;

use App\Models\Blog;
use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BlogMediaCountsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }

    public function handle() : void
    {
        $size = Media::where('blog_id', $this->blog->id)->sum('size');
        $this->blog->setCount('media', $size);
    }

    public function uniqueId() : int
    {
        return $this->blog->id;
    }
}
