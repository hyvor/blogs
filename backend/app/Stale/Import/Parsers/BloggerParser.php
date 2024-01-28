<?php

namespace App\Stale\Import\Parsers;

use App\Stale\Import\Repository;

class BloggerParser implements ParserInterface
{
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse(): Repository
    {
        dd('Blogger is working');
        $repo = new Repository();

        return $repo;
    }
}
