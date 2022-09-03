<?php

namespace App\Stale\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Stale\Export\Jobs\ExportJob;

/**
 * This repository is responsible for exporting content and media
 */
class ExportRepository
{
    public static function export(int $blogId, ExportFormatEnum $format = ExportFormatEnum::WORDPRESS)
    {
        dispatch(new ExportJob($blogId, $format));
    }
}
