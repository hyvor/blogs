<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\ParserInterface;
use App\Domains\Import\Repository;

class GhostParser implements ParserInterface
{
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse(): Repository
    {
        dd('Ghost is working');
        $repo = new Repository();

        return $repo;
    }
}
