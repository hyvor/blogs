<?php

namespace App\Service\Import\Importer;

use App\Entity\Blog;
use App\Service\Import\ImportLog;
use App\Service\Route\PermalinkService;

abstract class MediaAwareParserAbstract extends ParserAbstract
{
    public int $uploadsCount = 0;
    public int $duplicateCount = 0;

    abstract public function __construct(
        Blog $blog,
        string $path,
        ImportLog $log,
        PermalinkService $permalinkService,
    );

    abstract public function getMissingUploadsCount(): int;
}
