<?php

namespace App\Stale\Import\Parsers;

use App\Stale\Import\Repository;

interface ParserInterface
{
    public function __construct(string $file);

    public function parse(): Repository;
}
