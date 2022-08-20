<?php

namespace App\Domains\Blog\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class BlogDeletedEvent
{

    use Dispatchable;

    public function __construct(public Blog $blog) {}

}