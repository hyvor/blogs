<?php

namespace App\Domains\Theme;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ThemeCreationTypeEnum;
use App\Domains\Blog\BlogService;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ThemeRepository
{
    public static function getAllThemes(): Collection
    {
        return Theme::all();
    }

    public static function getAllThemesWithLatestVersions(): Collection
    {
        $themes = Theme::selectRaw('
            (
                SELECT id 
                FROM theme_versions 
                WHERE theme_id = themes.id 
                ORDER BY id DESC 
                LIMIT 1
            ) as latest_version_id, themes.*')
            ->get();

        $versionIds = $themes->map(fn ($theme) => $theme->latest_version_id);

        $versions = ThemeVersion::whereIn('id', $versionIds)->get();

        foreach ($themes as $theme) {
            $theme->setRelation('versions', [$versions->firstWhere('id', $theme->latest_version_id)]);
        }

        return $themes;
    }

    public static function getThemeByName(string $name): Theme
    {
        return Theme::where('name', $name)->first();
    }

    public static function getThemeLatestVersion(Theme $theme): ThemeVersion
    {
        return $theme->versions()->latest('id')->first();
    }

    public static function getThemeVersion(Theme $theme, string $version): ?ThemeVersion
    {
        return $theme->versions->where('version', $version)->first();
    }

    public static function createTheme(string $name, ThemeCreationTypeEnum $type): Theme
    {
        return Theme::create([
            'name' => $name,
            'type' => $type,
        ]);
    }

    public static function createThemeVersion(
        Theme $theme,
        string $version,
        string $zip,
    ) {
        $previewBlog = app(BlogService::class)->createBlog(
            null,
            $theme->name,
            self::generateThemePreviewSubdomain($theme->name, $version),
            BlogTypeEnum::PREVIEW
        );

        $theme->versions()->create([
            'version' => $version,
            'zip' => $zip,
            'preview_subdomain' => $previewBlog->subdomain,
        ]);

        ThemeFilesRepository::copyThemeToBlog($previewBlog, $theme->name);
    }

    private static function generateThemePreviewSubdomain(string $name, string $version)
    {
        $version = str_replace('.', '-', $version);
        $random = Str::random(12);

        return "theme-$name-$version-$random";
    }
}
