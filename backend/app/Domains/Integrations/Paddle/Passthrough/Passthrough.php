<?php

namespace App\Domains\Integrations\Paddle\Passthrough;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class Passthrough
{
    public static function encode(Blog $blog, ?string $referral = null) : bool | string
    {
        return json_encode([
            'blog_id' => $blog->id
        ]);
    }

    /**
     * Summary of decode
     * @param string $passthrough
     * @throws InvalidPassthroughException
     * @return Blog
     */
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

        /** @var ?Blog $blog */
        $blog = Blog::find($blogId);

        if (!$blog) {
            throw new InvalidPassthroughException();
        }

        return $blog;
    }
}
