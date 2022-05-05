<?php

namespace App\Domains\Theme;

/**
 * Downloads all themes from GitHub and updates the database
 */

class GithubSyncService
{
    public static function fetchAndUpdate()
    {
        $zipball = "https://github.com/hyvor/hyvor-blogs-themes/zipball/main";
        $zip = file_get_contents($zipball);

        dd($zip);
    }
}
