<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Theme\FileObject;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeImporter;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleThemeController extends Controller
{

    public function uploadTheme(Request $request, Blog $blog)
    {

        $request->validate([
            'zip' => 'required|file|max:' . config('limits.max_media_upload_size_kb'),
        ]);

        $zip = $request->file('zip');
        $content = $zip->getContent();

        ThemeFilesRepository::deleteAllFiles($blog);

        $importer = new ThemeImporter($blog, $content);
        $importer->import();

        if (!$importer->success()) {
            throw new TrustedException('Unable to import the theme');
        }

        return response()->json(ThemeFilesRepository::getAllFilesOfBlog($blog)->mapInto(FileObject::class));

    }

    public function getAllFiles(Blog $blog)
    {
        $files = ThemeFilesRepository::getAllFilesOfBlog($blog->id)->mapInto(FileObject::class);

        return response()->json($files);
    }
}
