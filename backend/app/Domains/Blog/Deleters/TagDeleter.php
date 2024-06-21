<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Database\Eloquent\Model;

class TagDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : ?Model
    {
        TagVariant::join('tags', 'tags.id', '=', 'tag_variants.tag_id')
            ->where('tags.blog_id', $this->blog->id)
            ->delete();

        Tag::where('blog_id', $this->blog->id)->delete();
    }
}
