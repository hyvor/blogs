<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Models\Blog;
use App\Models\Export;

class Exporter
{

    private Export $export;

    public function __construct(
        Blog $blog,
        ExportFormatEnum $format,
    )
    {
        $this->export = Export::create([
            'format' => $format,
            'blog_id' => $blog->id,
        ]);
    }



}