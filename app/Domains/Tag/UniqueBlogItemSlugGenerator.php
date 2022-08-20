<?php

namespace App\Domains\Tag;

use App\Domains\Shared\UniqueBlogItemSlugGeneratorAbstract;
use App\Models\Tag;

class UniqueBlogItemSlugGenerator extends UniqueBlogItemSlugGeneratorAbstract
{
    public function exists(string $slug): bool
    {
        return Tag::where('blog_id', $this->blog->id)->where('slug', $slug)->exists();
    }
}
