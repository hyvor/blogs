<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportJob implements ShouldBeUnique, ShouldQueue
{

    public function __construct(
        protected Blog $blog,
        protected ExportFormatEnum $format,
    ) {}

    public function handle() : void
    {
        $exporter = new Exporter($this->blog, $this->format);
        $exporter->export();
    }

    public function uniqueId() : int
    {
        return $this->blog->id;
    }

}