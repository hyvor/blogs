<?php

namespace App\Domains\Theme;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Models\Theme;
use App\Models\ThemeVersion;

class ThemeRepository
{

    public static function getThemeByName(string $name) : Theme
    {
        return Theme::where('name', $name)->first();
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
