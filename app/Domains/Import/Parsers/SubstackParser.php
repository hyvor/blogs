<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;

class SubstackParser implements ParserInterface
{
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse(): Repository
    {
        dd('substack is working');
        $repo = new Repository();

        return $repo;
    }
}
