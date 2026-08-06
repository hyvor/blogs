<?php

namespace App\Service\Export\Exporter;

use App\Entity\Blog;

interface ExporterInterface
{
    /**
     * Creates the export file locally and returns its local path.
     */
    public function createFile(Blog $blog): string;
}
