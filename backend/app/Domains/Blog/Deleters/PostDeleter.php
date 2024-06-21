<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Model;

class PostDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : ?Model
    {
        PostVariant::join('posts', 'posts.id', '=', 'post_variants.post_id')
            ->where('posts.blog_id', $this->blog->id)
            ->delete();

        Post::where('blog_id', $this->blog->id)->delete();
    }
}
