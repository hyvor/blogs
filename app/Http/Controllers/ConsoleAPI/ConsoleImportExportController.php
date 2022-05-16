<?php

namespace App\Http\Controllers\ConsoleAPI;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Import;
use App\Domains\Export\WordpressExporter;
use App\Http\Controllers\Controller;
use App\Data\Enums\ImportFormatEnum;
use App\Domains\Import\UploadRepository;
use  App\Domains\Import\Jobs\ImportJob;

<<<<<<< HEAD
class ConsoleImportExportController extends Controller {

    public function export(Blog $blog) {
=======
class ConsoleImportExportController extends Controller
{
    public function export(Blog $blog)
    {
>>>>>>> master
        $exporter = new WordpressExporter($blog->id);
        $data = $exporter->getFile();

        return response($data)->header('Content-Type', 'text/xml');
    }
<<<<<<< HEAD

    public function import(Request $request, Blog $blog, Import $import) {
        // $request->validate([
        //     'platform' => 'required|string',
        //     'file' => 'required|file',
        // ]);

        $platform = ImportFormatEnum::from($request->input('platform'));
        $file = $request->file('file');
       
        // $import = UploadRepository::uploadFile($platform, $blog, $file);
        dispatch(new ImportJob($platform, $blog, $import));
       
        // return response()->json($import);
    }
}
=======
}
>>>>>>> master
