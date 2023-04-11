<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ExportFormatEnum;
use App\Domains\Export\ExportJob;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class ConsoleImportExportController extends Controller
{
    public function export(Blog $blog) : JsonResponse
    {
        dispatch(new ExportJob($blog, ExportFormatEnum::HYVOR_BLOGS));
        return response()->json();
    }

    /*public function import(Request $request, Blog $blog, Import $import)
    {
        // $request->validate([
        //     'platform' => 'required|string',
        //     'file' => 'required|file',
        // ]);

        $platform = ImportFormatEnum::from($request->input('platform'));
        $file = $request->file('file');

        $import = UploadRepository::uploadFile($platform, $blog, $file);
        dispatch(new ImportJob($platform, $blog, $import));

        // return response()->json($import);
    }*/
}
