<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\ExportFormatEnum;
use App\Data\Enums\JobStatusEnum;
use App\Models\Export;

class ExportObject
{

    public int $id;
    public int $created_at;
    public ExportFormatEnum $format;
    public JobStatusEnum $status;
    public ?string $url;
    public ?string $error;

    public function __construct(Export $export)
    {
        $this->id = $export->id;
        $this->created_at = $export->created_at->getTimeStamp();
        $this->format = $export->format;
        $this->status = $export->status;
        $this->url = $export->url;
        $this->error = $export->error;
    }

}