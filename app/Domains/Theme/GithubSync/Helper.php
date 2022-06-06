<?php

namespace App\Domains\Theme\GithubSync;

use App\Models\Theme as ThemeModel;
use PhpZip\ZipFile;

class Helper
{

    /**
     * name => version
     * @return array<string, string>
     */
    public static function getLatestVersionsOfAllThemes() : array
    {

        // TODO: Use ThemeRepository function

        $themes = ThemeModel::selectRaw('
            (
                SELECT version 
                FROM theme_versions 
                WHERE theme_id = themes.id 
                ORDER BY id DESC 
                LIMIT 1
            ) as version, name')
            ->get();

        $ret = [];

        $themes->each(function ($theme) use (&$ret) {
            $ret[$theme->name] = $theme->version;
        });

        return $ret;

    }

    public static function generateZip(Theme $theme)
    {

        $zip = new ZipFile();

        foreach ($theme->files as $file) {
            $fileName = $file->folder === null ? $file->name : "{$file->folder->value}/$file->name";
            $zip->addFromString($fileName, $file->content);
        }

        return $zip->outputAsString();

    }

}