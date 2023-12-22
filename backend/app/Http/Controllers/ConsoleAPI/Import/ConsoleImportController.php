<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Import;

use App\Data\Objects\ConsoleAPI\Import\ImportObject;
use App\Domains\Import\ImportService;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class ConsoleImportController
{

    public function getImports(Blog $blog) : JsonResponse
    {
        $imports = ImportService::getImports($blog)->mapInto(ImportObject::class);
        return response()->json($imports);
    }

}