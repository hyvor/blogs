<?php

namespace App\Service\Export\Exporter;

interface ExporterInterface
{
    public function createFile(): string;
}
