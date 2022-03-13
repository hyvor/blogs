<?php

namespace App\Domains\Tag;

use App\Models\Tag;

class TagRepository
{
    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug) : ?Tag
    {
        $tag = Tag::where('blog_id', $blogId);
        if ($id) {
            $tag->where('id', $id);
        } else {
            $tag->where('slug', $slug);
        }
        return $tag->first();
    }

    public static function getTagByBlogIdAndSlug(int $blogId, string $slug) : ?Tag 
    {
        return self::getTagByBlogIdAndIdentifier($blogId, null, $slug);
    }
    
}
