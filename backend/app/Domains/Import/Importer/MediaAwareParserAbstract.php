<?php

namespace App\Domains\Import\Importer;

use App\Domains\App\JobMessageLog;
use App\Models\Blog;

abstract class MediaAwareParserAbstract extends ParserAbstract
{

    public int $uploadsCount = 0;
    public int $duplicateCount = 0;

    abstract public function __construct(
        Blog $blog,
        string $path,
        JobMessageLog $log,
    );

    abstract public function getMissingUploadsCount(): int;

}