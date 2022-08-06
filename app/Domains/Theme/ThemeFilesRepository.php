<?php

namespace App\Domains\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\Events\AssetEditedEvent;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\ThemeFile;
use Illuminate\Database\Eloquent\Collection;
use PhpZip\ZipFile;

class ThemeFilesRepository
{
    public static function getZip(Blog $blog): ZipFile
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
    ): Collection
    {
        return $blog->themeFiles()
            ->where('folder', $folder)
            ->get();
    }

    /**
     * @param  Blog  $blog
     * @return Collection<ThemeFile>
     */
    public static function getAllFilesOfBlog(Blog $blog): Collection
    {
        return $blog->themeFiles()->get();
    }

    public static function createOrUpdateFile(
        Blog $blog,
        ?ThemeFileFolderEnum $folder,
        string $name,
        string $content
    ) : ThemeFile
    {
        $file = $blog->themeFiles()->updateOrCreate(
            [
                'folder' => $folder,
                'name' => $name,
            ],
            [
                'content' => $content,
            ]
        );

        if ($folder === ThemeFileFolderEnum::STYLES) {
            StylesEditedEvent::dispatch($blog);
        }
        if ($folder === ThemeFileFolderEnum::ASSETS) {
            AssetEditedEvent::dispatch($blog, $name);
        }

        return $file;
    }

    /**
     * @param  ThemeFile  $file
     * @param  array{name?: string, content?: string}  $updates
     * @return void
     */
    public static function updateFile(ThemeFile $file, array $updates): ThemeFile
    {
        if (array_key_exists('name', $updates)) {
            $file->name = $updates['name'];
        }
        if (array_key_exists('content', $updates)) {
            $file->content = $updates['content'];
        }
        $file->save();

        return $file;
    }

    public static function deleteFile(ThemeFile $file)
    {
        $file->delete();
    }

    public static function deleteAllFiles(Blog $blog)
    {
        $blog->themeFiles()->delete();
    }

    public static function updateThemeFromZip(Blog $blog, string $zip): bool
    {
        self::deleteAllFiles($blog);

        $importer = new ThemeImporter($blog, $zip);
        $importer->import();

        return $importer->success();
    }

    public static function copyThemeToBlog(Blog $blog, string $themeName, string $version = null)
    {
        $theme = ThemeRepository::getThemeByName($themeName);

        $themeVersion = $version === null ?
            ThemeRepository::getThemeLatestVersion($theme) :
            ThemeRepository::getThemeVersion($theme, $version);

        if (! $themeVersion) {
            throw new TrustedException('Theme version not found');
        }

        $success = self::updateThemeFromZip($blog, $themeVersion->zip);

        if (! $success) {
            throw new TrustedException('Unable to copy the theme');
        }

        $blog->theme_version_id = $themeVersion->id;
        $blog->save();
    }

}
