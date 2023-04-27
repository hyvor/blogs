<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Domains\Export\Exceptions\ExportException;
use App\Models\Blog;

abstract class ExporterAbstract
{

    public function __construct(
        protected Blog $blog
    ) {}

    protected function getTemporaryFilePath(string $extension) : string
    {
        return sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
    }

    /**
     * Creates the export file and returns the file local path.
     * @return string
     */
    abstract public function createFile() : string;

}