<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;

interface ParserInterface
{
    public function __construct(string $file);

    public function parse(): Repository;
}
