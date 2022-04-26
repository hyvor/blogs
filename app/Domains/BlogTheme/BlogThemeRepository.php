<?php
/*
namespace App\Domains\BlogTheme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use App\Models\ThemeFile;
use Database\Seeders\BlogThemeFilesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

class ThemeFilesRepository
{
    public static function getFile(Blog $blog, string $fileName, ?ThemeFileFolderEnum $folder = null): ?ThemeFile
    {
        dd("DAW");
        return $blog->themeFiles()
            ->where('name', $fileName)
            ->where('folder', $folder->value)
            ->first();

    }

    public static function getMultipleFiles(
        Blog $blog, array $fileNames, 
        ?ThemeFileFolderEnum $folder = null
    ) : Collection
    {
        return $blog->themeFiles()
            ->whereIn('name', $fileNames)
            ->where('folder', $folder->value)
            ->get();
    }

    public static function getFilesInFolder(Blog $blog, ?ThemeFileFolderEnum $folder): Collection
    {
        self::updateLocalDBFiles($blog->id);

        return $blog->themeFiles()
            ->where('folder', $folder)
            ->get();
    }

    public static function getAllFiles(int $blogId) : Collection 
    {
        self::updateLocalDBFiles($blogId);

        return BlogThemeFile::where('blog_id', $blogId)->get();
    }

    private static function updateLocalDBFiles(int $blogId) 
    {

        /**
         * This is a simple way to refresh the database
         * and run the seeder that so local file changes are updated
         * This is ONLY FOR LOCAL TESTING
         
        if (App::environment('local')) {
            BlogThemeFile::where('blog_id', $blogId)->delete();
            (new BlogThemeFilesSeeder())->run($blogId);
        }

    }

}*/
