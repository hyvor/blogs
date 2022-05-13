<?php
namespace App\Http\Controllers\ConsoleAPI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog;
use App\Models\Import;
use App\Domains\Export\WordpressExporter;
use App\Http\Controllers\Controller;
use App\Data\Enums\ImportFormatEnum;
use App\Domains\Import\UploadRepository;
use  App\Domains\Import\Jobs\ImportJob;

class ConsoleImportExportController extends Controller {

    public function export(Blog $blog) {
        $exporter = new WordpressExporter($blog->id);
        $data = $exporter->getFile();
        return response($data)->header('Content-Type', 'text/xml');
    }

    public static function uploadFile(Request $request, Blog $blog){
        $file = $request->file('file');
        $request->validate([
            'file' => 'required|file',
        ]);

        $import = UploadRepository::uploadFile($blog, $file);
        return response()->json($import);
    }

    public function import(Request $request, Blog $blog, Import $import) {
        $platform = ImportFormatEnum::from($request->input('platform'));
        $request->validate([
            'platform' => 'required|string',
        ]);

        $fileName = Import::select('name')
            ->where('blog_id','=', $blog->id)
            ->value('name');
    
        // $wordpressPath = Storage::get('import\'.$fileName);
        $file = Storage::get('public\wordpress.xml');
        dispatch(new ImportJob($platform, $file, $blog, $import));
        // return response()->json($import);
    }
}