<?php

namespace App\Domains\Theme;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Eloquent\Collection;

class ThemeRepository
{

    public static function getAllThemes() : Collection
    {
        return Theme::all();
    }

    public static function getThemeByName(string $name) : Theme
    {
        return Theme::where('name', $name)->first();
    }

    public static function getThemeLatestVersion(Theme $theme) : ThemeVersion
    {
        return $theme->versions()->latest('id')->first();
    }

    public static function getThemeVersion(Theme $theme, string $version) : ?ThemeVersion
    {
        return $theme->versions->where('version', $version)->first();
    }

    public static function createTheme(string $name, ThemeCreationTypeEnum $type) : Theme
    {

        return Theme::create([
            'name' => $name,
            'type' => $type
        ]);

    }

    public static function createThemeVersion(
        Theme $theme,
        string $version,
        string $zip,
    )
    {

        $theme->versions()->create([
            'version' => $version,
            'zip' => $zip
        ]);

    }

}
