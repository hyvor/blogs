<?php
namespace App\Domains\Export;

class JsonExporter implements ExporterInterface {


    public function __construct(int $blogId) 
    {
        $this->blogId = $blogId;
    }

    public function getFile()
    {
     
        


    }

}