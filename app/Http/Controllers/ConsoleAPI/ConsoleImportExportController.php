<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Export\WordpressExporter;
use App\Http\Controllers\Controller;
use App\Models\Blog;

use Illuminate\Http\Request;
use App\Data\Enums\ImportFormatEnum;
use App\Domains\Import\Jobs\ImportJob;

// First file should be uploaded and then save it in the storage folder.
// The data of the file ( name, platform ) should be saved in the database.
// Then the importing should be done when the user clicks the import button  from the front-end
// keep in mind these are 02 different functions.

class ConsoleImportExportController extends Controller {

    public function export(Blog $blog) {
        $exporter = new WordpressExporter($blog->id);
        $data = $exporter->getFile();
        return response($data)->header('Content-Type', 'text/xml');
    }

    public static function uploadFile(Request $request, Blog $blog)
    {
        $file = $request->file('file');
        // $platform = ImportFormatEnum::from($request->input('platform'));

        // $request->validate([
        //     'file' => 'required|file',
        //     'platform' => 'required|string',
        // ]);

        $import = ImportJob::uploadFile($blog->id, $file);
        return response()->json($import);
    }

    public function import(Blog $blog) {
    }
}