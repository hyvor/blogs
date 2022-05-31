<?php

namespace App\Domains\_Shared;

use App\Models\Blog;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Generates a unique slug for users and tags
 */
abstract class UniqueSlugGeneratorAbstract
{

    public function __construct(protected Blog $blog) {}

    abstract public function exists(string $slug) : bool;

    /**
     * It checks if the slug versions of the given strings are
     * unique, otherwise, returns a random string
     *
     * @param string[] $checks
     * @return string
     */
    public function generateSlug(array $checks)
    {
        foreach ($checks as $check) {
            $slug = Str::slug($check);

            if (!$this->exists($slug)) {
                return $slug;
            }
        }

        return Str::random();
    }

    /**
     * @param Blog $blog
     * @param string[] $checks
     */
    public static function generate(Blog $blog, array $checks)
    {
        $generator = new static($blog);
        return $generator->generateSlug($checks);
    }

}