<?php

namespace App\Domains\Integrations\Paddle\Passthrough;

use App\Models\Blog;
use http\Exception\InvalidArgumentException;

class Passthrough
{
    public static function encode(Blog $blog)
    {
        return json_encode([
            'blog_id' => $blog->id
        ]);
    }

    public static function decode(string $passthrough): Blog
    {
        $json = json_decode($passthrough);

        if (!$json) {
            throw new InvalidPassthroughException();
        }

        $blogId = $json->blog_id ?? null;

        if (!$blogId) {
            throw new InvalidPassthroughException();
        }

        $blog = Blog::find($blogId);

        if (!$blog) {
            throw new InvalidArgumentException();
        }

        return $blog;
    }
}
