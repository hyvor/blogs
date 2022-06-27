<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\ThemeFile;

class ThemeDeleter implements DeleterInterface
{

    public function __construct(private Blog $blog)
    {
    }

    public function delete()
    {
        ThemeFile::where('blog_id', $this->blog->id)->delete();
    }

}