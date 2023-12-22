<?php declare(strict_types=1);

namespace App\Domains\Blog\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class BlogUpdatedEvent
{
    use Dispatchable;

    public function __construct(
        public Blog $blog,
        public Blog $blogOriginal
    )
    {}

}
