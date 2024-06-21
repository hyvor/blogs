<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\ThemeFile;
use Illuminate\Database\Eloquent\Model;

class ThemeDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : ?Model
    {
        ThemeFile::where('blog_id', $this->blog->id)->delete();
    }
}
