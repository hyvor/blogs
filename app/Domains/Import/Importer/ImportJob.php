<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Models\Blog;
use App\Models\Import;

class ImportJob
{

    public function __construct(
        private readonly Blog $blog,
        private readonly Import $import,
        private readonly ParserAbstract $parser,
        private readonly bool $importImages,
    ) {}

    public function handle()
    {
        $importer = new Importer(
            $this->blog,
            $this->import,
            $this->parser,
            $this->importImages
        );
        $importer->import();
    }

}