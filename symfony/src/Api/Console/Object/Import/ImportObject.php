<?php

namespace App\Api\Console\Object\Import;

use App\Entity\Enum\ImportType;
use App\Entity\Enum\JobStatus;
use App\Entity\Import;

class ImportObject
{
    public int $id;
    public int $created_at;
    public ?string $name;
    public ImportType $type;
    public JobStatus $status;

    /** @var array<mixed> */
    public array $options;

    public ?string $error;

    public ImportedCountsObject $imported_counts;

    public function __construct(Import $import)
    {
        $this->id = $import->getId();
        $this->created_at = $import->getCreatedAt()?->getTimestamp() ?? 0;
        $this->name = $import->getName();
        $this->type = $import->getType();
        $this->status = $import->getStatus();

        $this->options = $import->getOptions() ?? [];
        $this->error = $import->getError();

        $this->imported_counts = new ImportedCountsObject(
            posts: $import->getPostsCount(),
            pages: $import->getPagesCount(),
            tags: $import->getTagsCount(),
            users: $import->getUsersCount(),
        );
    }
}
