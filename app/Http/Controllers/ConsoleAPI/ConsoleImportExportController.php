<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ImportFormatEnum;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Import;
use App\Stale\Export\WordpressExporter;
use App\Stale\Import\Jobs\ImportJob;
use App\Stale\Import\UploadRepository;
use Illuminate\Http\Request;

class ConsoleImportExportController extends Controller
{
    public function export(Blog $blog)
    {
        $exporter = new WordpressExporter($blog->id);
        $data = $exporter->getFile();

        return response($data)->header('Content-Type', 'text/xml');
    }

    public function import(Request $request, Blog $blog, Import $import)
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
    }
}
