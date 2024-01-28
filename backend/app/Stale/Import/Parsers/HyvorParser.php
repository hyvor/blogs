<?php

namespace App\Stale\Import\Parsers;

use App\Stale\Import\Repository;

class HyvorParser implements ParserInterface
{
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse(): Repository
    {
        dd('hyvor is working');
        $repo = new Repository();

        return $repo;
    }
}
