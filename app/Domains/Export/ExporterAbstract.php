<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Domain\Export\Exceptions\ExportException;
use App\Models\Blog;

abstract class ExporterAbstract
{

    public function __construct(
        protected Blog $blog
    ) {}

    protected function getTemporaryFilePath(string $extension) : string
    {
        $file = tmpfile();

        if (!$file)
            throw new ExportException('Could not create temporary file.');

        $meta = stream_get_meta_data($file);
        $path = $meta['uri'];
        fclose($file);
        return $path . '.' . $extension;
    }

    /**
     * Creates the export file and returns the file local path.
     * @return string
     */
    abstract public function createFile() : string;

}