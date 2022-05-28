<?php

namespace App\Domains\Theme;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use App\Models\ThemeFile;
use Database\Seeders\BlogThemeFilesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use PhpZip\ZipFile;

class ThemeFilesRepository
{

    public static function getZip(Blog $blog) : ZipFile
    {

        $zip = new ZipFile();

        $files = self::getAllFilesOfBlog($blog);

        foreach ($files as $file) {

            $entryName = $file->folder === null ? $file->name : "{$file->folder->value}/$file->name";
            $zip->addFromString($entryName, $file->content);

        }

        return $zip;

    }

    public static function getFile(
        Blog $blog,
        string $fileName,
        ?ThemeFileFolderEnum $folder = null
    ): ?ThemeFile {
        return $blog->themeFiles()
            ->where('name', $fileName)
            ->where('folder', $folder ? $folder->value : null)
            ->first();
    }

    public static function getMultipleFiles(
        Blog $blog,
        array $fileNames,
        ?ThemeFileFolderEnum $folder = null
    ): Collection {
        return $blog->themeFiles()
            ->whereIn('name', $fileNames)
            ->where('folder', $folder->value)
            ->get();
    }

    public static function getFilesInFolder(
        Blog $blog,
        ?ThemeFileFolderEnum $folder
    ): Collection {
        self::updateLocalDBFiles($blog->id);

        return $blog->themeFiles()
            ->where('folder', $folder)
            ->get();
    }

    /**
     * @param Blog $blog
     * @return Collection<ThemeFile>
     */
    public static function getAllFilesOfBlog(Blog $blog): Collection
    {
        self::updateLocalDBFiles($blog->id);

        return $blog->themeFiles()->get();
    }

    public static function createOrUpdateFile(
        Blog $blog,
        ?ThemeFileFolderEnum $folder,
        string $name,
        $content
    ): void {
        $blog->themeFiles()->updateOrCreate(
            [
                'folder' => $folder,
                'name' => $name,
            ],
            [
                'content' => $content,
            ]
        );
    }

    public static function deleteAllFiles(Blog $blog)
    {
        $blog->themeFiles()->delete();
    }

    private static function updateLocalDBFiles(int $blogId)
    {

        return;

        /**
         * This is a simple way to refresh the database
         * and run the seeder that so local file changes are updated
         * This is ONLY FOR LOCAL TESTING
         */
        $blog = Blog::find($blogId);
        if (
            App::environment('local') &&
            $blog->type !== BlogTypeEnum::DEV
        ) {
            self::deleteAllFiles($blog);
            (new BlogThemeFilesSeeder())->run($blogId);
        }
    }
}
