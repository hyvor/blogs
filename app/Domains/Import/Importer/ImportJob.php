<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Data\Enums\JobStatusEnum;
use App\Domains\Import\ImportException;
use App\Models\Blog;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class ImportJob implements ShouldQueue
{

    public int $timeout = 0;

    public function __construct(
        public readonly Blog $blog,
        public readonly Import $import,
        public readonly ParserAbstract $parser,
        public readonly bool $importImages,
    ) {}

    public function handle() : void
    {
        $importer = new Importer(
            $this->blog,
            $this->parser,
            $this->importImages
        );
        $importer->import();

        $this->import->update([
            'status' => JobStatusEnum::COMPLETED,
            'posts_count' => $importer->postsCount
        ]);
    }

    public function failed(Throwable $e) : void
    {
        $this->import->update([
            'status' => JobStatusEnum::FAILED,
            'error' => $e instanceof ImportException ?
                $e->getMessage() :
                'Unknown error'
        ]);
    }

}