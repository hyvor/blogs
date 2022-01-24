<?php

namespace App\Domains\Eloquent;

use App\Domains\ImportExportRepositoryInterface;

class ImportExportRepository implements ImportExportRepositoryInterface
{
    public function index($final)
    {

        // $testFinal = json_encode($final);
        dd($final);
    }
}
