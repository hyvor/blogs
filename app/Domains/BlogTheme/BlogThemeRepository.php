<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\BlogThemeFile;
use Database\Seeders\BlogThemeFilesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

class BlogThemeRepository
{
    public static function getFile(int $blogId, string $fileName, ?string $folder = null): ?BlogTHemeFile
    {

        $file = BlogThemeFile::where('blog_id', $blogId)
            ->where('name', $fileName)
            ->where('folder', $folder)
            ->first();

        return $file;
    }

    public static function getFilesInFolder(int $blogId, ?ThemeFileFolderEnum $folder): Collection
    {
        
        /**
         * This is a simple way to refresh the database
         * and run the seeder that so local file changes are updated
         * This is ONLY FOR LOCAL TESTING
         */
        if (App::environment('local')) {
            BlogThemeFile::where('blog_id', $blogId)->delete();
            (new BlogThemeFilesSeeder())->run();
        }

        return BlogThemeFile::where('blog_id', $blogId)
            ->where('folder', $folder)
            ->get();
    }
}
