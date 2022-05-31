<?php

namespace App\Domains\Theme;
use App\Domains\Theme\Object\ThemeRepositoryObject;
use App\Models\Theme;
use App\Models\ThemeVersion;

class ThemeRepository
{
    public static function createTheme($value)
    {
        // return Theme::create([
        //     'name' => $value['themeName'],
        //     'type' => $value['type'],
        // ]);
    }

    public static function createThemeVersion($yaml)
    {
        $name = $yaml['theme_name'];
        $id = Theme::where('name', $name)->value('id');
        
        // ThemeVersion::create([
        //     'theme_id' => $id,
        //     'version' => $yaml['theme_version'],
        // ]);
    }

    public static function publishNewVersion()
    {
    }

    public static function getThemeName($themeName)
    {
        return Theme::where('name', $themeName)->value('name');
    }
}
