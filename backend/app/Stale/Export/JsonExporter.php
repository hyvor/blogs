<?php

namespace App\Stale\Export;

class JsonExporter implements ExporterInterface
{
    public function __construct(int $blogId)
    {
        $this->blogId = $blogId;
    }

    public function getFile()
    {
    }
}
