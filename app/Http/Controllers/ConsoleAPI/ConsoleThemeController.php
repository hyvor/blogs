<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\ConsoleAPI\Theme\FileObject;
use App\Data\Objects\ConsoleAPI\Theme\ThemeObject;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\ThemeFile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

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
            'name' => 'required',
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

        if (! $success) {
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

    public function createFile(Request $request, Blog $blog)
    {
        $request->validate([
            'folder' => ['required', 'nullable', new Enum(ThemeFileFolderEnum::class)],
            'name' => 'required|string',
            'content' => 'string|nullable',
            'file' => 'file'
        ]);

        $folder = ThemeFileFolderEnum::tryFrom($request->input('folder'));
        $name = $request->input('name');
        $content = $request->input('content') ?? '';

        if ($request->has('file')) {
            $file = $request->file('file');
            $content = $file->get();
        }

        if (ThemeFilesRepository::getFile($blog, $name, $folder)) {
            throw new TrustedException('File already exists');
        }

        $file = ThemeFilesRepository::createOrUpdateFile(
            $blog,
            $folder,
            $name,
            $content
        );

        return response()->json(new FileObject($file));
    }

    public function updateFile(Request $request, ThemeFile $file)
    {

        $request->validate([
            'name' => 'string',
            'content' => 'string|nullable'
        ]);

        $updates = [];

        if ($request->has('name')) {
            $updates['name'] = $request->input('name');
        }
        if ($request->has('content')) {
            $updates['content'] = $request->input('content');
        }

        $file = ThemeFilesRepository::updateFile($file, $updates);

        return response()->json(new FileObject($file));
    }

    public function deleteFile(ThemeFile $file)
    {
        ThemeFilesRepository::deleteFile($file);
        return response()->json();
    }

    public function getAllFiles(Blog $blog)
    {
        $files = ThemeFilesRepository::getAllFilesOfBlog($blog)->mapInto(FileObject::class);

        return response()->json($files);
    }
}
