<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\ConsoleAPI\Theme\FileObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class ConsoleBlogThemeController extends Controller {

    public function getAllFiles(Blog $blog) {

        $files = BlogThemeRepository::getAllFiles($blog->id)->map(function($file) {
            return new FileObject($file);
        });

        return response()->json($files);

    }

}