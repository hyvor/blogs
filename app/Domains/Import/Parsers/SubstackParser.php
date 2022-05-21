<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;
use App\Models\Blog;
use App\Domains\Import\ParserInterface;

class SubstackParser implements ParserInterface
{
    public function __construct(public string $file){
        $this->file = $file;
    }

    public function parse() : Repository{

        dd('substack is working');
        $repo = new Repository();
        return $repo;
    }
}