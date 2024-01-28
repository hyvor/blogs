<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Redirect;

class RedirectDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete()
    {
        Redirect::where('blog_id', $this->blog->id)->delete();
    }
}
