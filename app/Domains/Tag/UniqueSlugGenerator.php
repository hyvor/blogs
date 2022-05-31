<?php

namespace App\Domains\Tag;

use App\Domains\_Shared\UniqueSlugGeneratorAbstract;
use App\Models\Tag;

class UniqueSlugGenerator extends UniqueSlugGeneratorAbstract
{
    public function exists(string $slug): bool
    {
        return Tag::where('blog_id', $this->blog->id)->where('slug', $slug)->exists();
    }
}
