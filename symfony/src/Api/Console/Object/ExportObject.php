<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\ExportFormat;
use App\Entity\Enum\JobStatus;
use App\Entity\Export;

class ExportObject
{
    public int $id;
    public int $created_at;
    public ExportFormat $format;
    public JobStatus $status;
    public ?string $url;
    public ?string $error;

    public function __construct(Export $export)
    {
        $this->id = $export->getId();
        $this->created_at = $export->getCreatedAt()->getTimestamp();
        $this->format = $export->getFormat();
        $this->status = $export->getStatus();
        $this->url = $export->getUrl();
        $this->error = $export->getError();
    }
}
