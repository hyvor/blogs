<?php
namespace App\Domains\Export\Jobs;

use App\Data\Enums\ExportFormatEnum;
use App\Domains\Export\ExporterInterface;
use App\Domains\Export\WordpressExporter;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportJob implements ShouldQueue {

    protected int $blogId;
    protected ExporterInterface $exporter;

    public function __construct(int $blogId, ExportFormatEnum $format) 
    {
        $this->blogId = $blogId;

        $exporterClass = match($format) {
            ExportFormatEnum::WORDPRESS => WordpressExporter::class,
            ExportFormatEnum::JSON => JSONExporter::class
        };

        $this->exporter = new $exporterClass($blogId);
    }

    public function handle() {
        $file = $this->exporter->getFile();
    }

}