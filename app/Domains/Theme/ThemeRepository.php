<?php

namespace App\Domains\Theme;
use App\Domains\Theme\Object\ThemeRepositoryObject;
use App\Models\Theme;

class ThemeRepository
{
    public static function createTheme($value)
    {
        // return Theme::create([
        //     'name' => $value['themeName'],
        //     'type' => $value['type'],
        // ]);
    }

    public static function createThemeVersion()
    {
    }

    public static function publishNewVersion()
    {
    }

    public static function getThemeName($themeName)
    {
        return Theme::where('name', $themeName)->value('name');
    }
}
