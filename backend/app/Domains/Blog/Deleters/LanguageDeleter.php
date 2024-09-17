<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

class LanguageDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : void
    {
        Language::where('blog_id', $this->blog->id)->delete();
    }
}
