<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;
use App\Models\Blog;
use App\Domains\Import\ParserInterface;

class GhostParser implements ParserInterface
{
    public function __construct(public string $file){
        $this->file = $file;
    }

    public function parse() : Repository{

        dd('Ghost is working');
        $repo = new Repository();
        return $repo;
    }
}