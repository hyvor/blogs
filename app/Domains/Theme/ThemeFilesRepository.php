<?php declare(strict_types=1);

namespace App\Domains\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\Events\AssetEditedEvent;
use App\Domains\Theme\Events\ConfigEditedEvent;
use App\Domains\Theme\Events\LangEditedEvent;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Domains\Theme\Events\TemplateEditedEvent;
use App\Domains\Theme\GithubSync\File;
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
            $zip->addFromString($entryName, $file->content ?? '');
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

    /**
     * @param string[] $fileNames
     * @return Collection<int, ThemeFile>
     */
    public static function getMultipleFiles(
        Blog $blog,
        array $fileNames,
        ?ThemeFileFolderEnum $folder = null
    ): Collection
    {
        return $blog->themeFiles()
            ->whereIn('name', $fileNames)
            ->where('folder', $folder?->value)
            ->get();
    }

    /**
     * @return Collection<int, ThemeFile>
     */
    public static function getFilesInFolder(
        Blog $blog,
        ?ThemeFileFolderEnum $folder
    ): Collection {
        return $blog->themeFiles()
            ->where('folder', $folder)
            ->get();
    }

    /**
     * @param  Blog  $blog
     * @return Collection<int, ThemeFile>
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
    ): ThemeFile {
        $file = $blog->themeFiles()->updateOrCreate(
            [
                'folder' => $folder,
                'name' => $name,
            ],
            [
                'content' => $content,
            ]
        );

        self::fileUpdated($file);

        return $file;
    }

    /**
     * @param  ThemeFile  $file
     * @param  array{name?: string, content?: string}  $updates
     * @return ThemeFile
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

        self::fileUpdated($file);

        return $file;
    }

    private static function fileUpdated(ThemeFile $file) : void
    {
        /** @var Blog $blog */
        $blog = $file->blog;

        match ($file->folder) {
            ThemeFileFolderEnum::STYLES => StylesEditedEvent::dispatch($blog),
            ThemeFileFolderEnum::ASSETS => AssetEditedEvent::dispatch($blog, $file->name),
            ThemeFileFolderEnum::TEMPLATES => TemplateEditedEvent::dispatch($file),
            ThemeFileFolderEnum::LANG => LangEditedEvent::dispatch($file),
            null => $file->name === 'config.yaml' ? ConfigEditedEvent::dispatch($file) : null,
            default => null
        };
    }

    public static function deleteFile(ThemeFile $file) : void
    {
        $file->delete();
    }

    public static function deleteAllFiles(Blog $blog) : void
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

    public static function copyThemeToBlog(Blog $blog, string $themeName, string $version = null) : void
    {
        $theme = ThemeRepository::getThemeByName($themeName);

        if (!$theme)
            return;

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
