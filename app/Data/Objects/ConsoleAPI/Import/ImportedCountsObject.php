<?php

namespace App\Data\Objects\ConsoleAPI\Import;

class ImportedCountsObject
{

    public function __construct(
        public int $posts,
        public int $pages,
        public int $tags,
        public int $users,
    ) {}

}