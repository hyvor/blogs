<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ExportFormatEnum;
use App\Data\Objects\ConsoleAPI\ExportObject;
use App\Domains\Export\ExportJob;
use App\Domains\Export\ExportService;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class ConsoleExportController extends Controller
{

    public function getExports(Blog $blog): JsonResponse
    {
        $exports = ExportService::getExports($blog)->mapInto(ExportObject::class);
        return response()->json($exports);
    }

    public function export(Blog $blog): JsonResponse
    {

        if (ExportService::hasPendingExports($blog)) {
            throw new TrustedException('There is already a pending export for this blog. Please wait until it is finished.');
        }

        $export = ExportService::createExport($blog, ExportFormatEnum::HYVOR_BLOGS);

        dispatch(new ExportJob(
            $blog,
            ExportFormatEnum::HYVOR_BLOGS,
            $export
        ));

        return response()->json(new ExportObject($export));
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
