<?php

namespace App\Domains\Shared;

use App\Models\Blog;
use Illuminate\Support\Str;

/**
 * Generates a unique slug for users and tags
 */
abstract class UniqueBlogItemSlugGeneratorAbstract
{
    use UniqueSlugGeneratorTrait;

    public function __construct(protected Blog $blog)
    {
    }

    /**
     * @param  Blog  $blog
     * @param  string[]  $checks
     */
    public static function generate(Blog $blog, array $checks)
    {
        $generator = new static($blog);

        return $generator->generateSlug($checks);
    }
}
