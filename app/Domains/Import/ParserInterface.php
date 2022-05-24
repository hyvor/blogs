<?php

namespace App\Domains\Import;

interface ParserInterface
{
    public function __construct(string $file);
    public function parse() : Repository;
}