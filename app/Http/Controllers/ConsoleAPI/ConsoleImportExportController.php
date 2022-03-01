<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Export\WordpressExporter;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class ConsoleImportExportController extends Controller {

    public function export(Blog $blog) {

        $exporter = new WordpressExporter($blog->id);
        $data = $exporter->getFile();
        return response($data)->header('Content-Type', 'text/xml');

    }

}