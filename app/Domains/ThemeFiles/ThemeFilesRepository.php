<?php

namespace App\Domains\ThemeFiles;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use App\Models\LocalDev;
use App\Models\ThemeFile;
use Database\Seeders\BlogThemeFilesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

class ThemeFilesRepository
{

    public static function getFile(
        Blog|LocalDev $themable,
        string $fileName, 
        ?ThemeFileFolderEnum $folder = null): ?ThemeFile
    {

        return $themable->themeFiles()
            ->where('name', $fileName)
            ->where('folder', $folder->value)
            ->first();

    }

    public static function getMultipleFiles(
        Blog|LocalDev $themable, array $fileNames, 
        ?ThemeFileFolderEnum $folder = null
    ) : Collection
    {
        return $themable->themeFiles()
            ->whereIn('name', $fileNames)
            ->where('folder', $folder->value)
            ->get();
    }

    public static function getFilesInFolder(
        Blog|LocalDev $themable, 
        ?ThemeFileFolderEnum $folder
    ) : Collection
    {
        self::updateLocalDBFiles($themable->id);

        return $themable->themeFiles()
            ->where('folder', $folder)
            ->get();
    }

    public static function getAllFilesOfBlog(int $blogId) : Collection 
    {
        self::updateLocalDBFiles($blogId);

        return ThemeFile::where('themable_id', $blogId)
            ->where('themable_type', Blog::class)
            ->get();
    }

    public static function createOrUpdateFile(
        Blog|LocalDev $themable, 
        ?ThemeFileFolderEnum $folder,
        string $name,
        $content
    ) : void 
    {

        $themable->themeFiles()->updateOrCreate(
            [
                'folder' => $folder,
                'name' => $name,
            ],
            [
                'content' => $content
            ]
        );

    }

    private static function updateLocalDBFiles(int $blogId) {
        
        /**
         * This is a simple way to refresh the database
         * and run the seeder that so local file changes are updated
         * This is ONLY FOR LOCAL TESTING
         */
        if (App::environment('local')) {
            Blog::find($blogId)->themeFiles()->delete();
            (new BlogThemeFilesSeeder())->run($blogId);
        }

    }

}
