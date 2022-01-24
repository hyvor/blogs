<?php

namespace App\Domains\BlogTheme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

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

        return BlogThemeFile::where('blog_id', $blogId)
            ->where('folder', $folder)
            ->get();
    }
}
