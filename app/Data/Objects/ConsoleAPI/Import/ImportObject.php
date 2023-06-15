<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Import;

use App\Data\Enums\ImportTypeEnum;
use App\Data\Enums\JobStatusEnum;
use App\Models\Import;

class ImportObject
{

    public int $id;
    public int $created_at;
    public ?string $name;
    public ImportTypeEnum $type;
    public JobStatusEnum $status;

    public ImportedCountsObject $imported_counts;

    public function __construct(Import $import)
    {

        $this->id = $import->id;
        $this->created_at = (int) $import->created_at?->getTimestamp();
        $this->name = $import->name;
        $this->type = $import->type;
        $this->status = $import->status;

        $this->imported_counts = new ImportedCountsObject(
            posts: $import->posts_count,
            pages: $import->pages_count,
            tags: $import->tags_count,
            users: $import->users_count
        );

    }

}