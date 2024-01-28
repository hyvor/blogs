<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Models\Blog;
use App\Models\Export;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportJob implements ShouldQueue
{

    public function __construct(
        protected Blog $blog,
        protected ExportFormatEnum $format,
        protected ?Export $export = null,
    ) {}

    public function handle() : void
    {
        $exporter = new Exporter($this->blog, $this->format, $this->export);
        $exporter->export();
    }

}