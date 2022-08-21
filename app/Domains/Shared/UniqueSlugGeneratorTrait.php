<?php

namespace App\Domains\Shared;

use Illuminate\Support\Str;

trait UniqueSlugGeneratorTrait
{

    abstract public function exists(string $slug) : bool;

    /**
     * It checks if the slug versions of the given strings are
     * unique, otherwise, returns a random string
     *
     * @param  string[]  $checks
     * @return string
     */
    public function generateSlug(array $checks) : string
    {
        $i = 0;
        while (true) {
            $check = $checks[$i] ?? Str::random();
            $slug = Str::slug($check);

            if (!$this->exists($slug)) {
                return $slug;
            }
            $i++;
        }
    }

    public static function generate(array $checks) : string
    {
        $generator = new static();
        return $generator->generateSlug($checks);
    }

}