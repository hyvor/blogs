<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Theme\FileObject;
use App\Data\Objects\ConsoleAPI\Theme\ThemeObject;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeImporter;
use App\Domains\Theme\ThemeRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleThemeController extends Controller
{

    public function getAllThemes()
    {
        $all = ThemeRepository::getAllThemes();
        return response()->json($all->mapInto(ThemeObject::class));
    }

    public function changeTheme(Request $request, Blog $blog)
    {

        $request->validate([
            'name' => 'required'
        ]);

        $name = $request->input('name');

        ThemeFilesRepository::copyThemeToBlog($blog, $name);

        return response()->json(ThemeFilesRepository::getAllFilesOfBlog($blog)->mapInto(FileObject::class));
    }

    public function uploadTheme(Request $request, Blog $blog)
    {
        $request->validate([
            'zip' => 'required|file|max:' . config('limits.max_media_upload_size_kb'),
        ]);

        $zip = $request->file('zip');
        $content = $zip->getContent();

        $success = ThemeFilesRepository::updateThemeFromZip($blog, $content);

        if (!$success) {
            throw new TrustedException('Unable to import the theme');
        }

        return response()->json(ThemeFilesRepository::getAllFilesOfBlog($blog)->mapInto(FileObject::class));
    }

    public function downloadTheme(Blog $blog)
    {
        $zip = ThemeFilesRepository::getZip($blog);

        $filename = 'hb-theme-of-' . $blog->subdomain . '-' . date('Y-m-d') . '.zip';

        return $zip->outputAsSymfonyResponse($filename, 'application/zip');
    }

    public function getAllFiles(Blog $blog)
    {
        $files = ThemeFilesRepository::getAllFilesOfBlog($blog)->mapInto(FileObject::class);

        return response()->json($files);
    }
}
