<?php

namespace App\Stale\Export;

interface ExporterInterface
{
    public function __construct(int $blogId);

    public function getFile();
}
