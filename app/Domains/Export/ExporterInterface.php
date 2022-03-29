<?php
namespace App\Domains\Export;

interface ExporterInterface {

    public function __construct(int $blogId);
    public function getFile();

}