<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Models\Blog;
use App\Models\Import;

class Importer
{

    public function __construct(
        private readonly Blog $blog,
        private readonly Import $import,
        private readonly ParserAbstract $parser
    ) {}

    public function import() : void
    {

        $this->parser->parse();

    }

}