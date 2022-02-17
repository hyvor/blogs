<?php

namespace App\Domains\Tag;

use App\Models\Tag;

class TagRepository
{
    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug)
    {
        $post = Tag::where('blog_id', $blogId);
        if ($id) {
            $post->where('id', $id);
        } else {
            $post->where('slug', $slug);
        }
        return $post->first();
    }

    
}
