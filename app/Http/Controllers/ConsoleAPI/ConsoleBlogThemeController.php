<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Theme\FileObject;
use App\Domains\Theme\ThemeFilesRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class ConsoleBlogThemeController extends Controller {

    public function getAllFiles(Blog $blog) {

        $files = ThemeFilesRepository::getAllFilesOfBlog($blog->id)->map(function($file) {
            return new FileObject($file);
        });

        return response()->json($files);

    }

}