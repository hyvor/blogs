<?php

namespace App\Console\Commands\Tests;

use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Console\Command;
use PhpZip\ZipFile;

class GenerateThemesZip extends Command
{
    protected $signature = 'tests:generate-themes-zip';

    protected $description = 'Generates the themes zip with blank and default themes';

    public function handle()
    {
        $zipball = 'https://github.com/hyvor/hyvor-blogs-themes/zipball/main';
        $zip = file_get_contents($zipball);

        $sync = new GithubSyncService($zip);
        $sync->breakIntoThemes();

        $themes = $sync->themes;

        // only blank and default
        $themeNames = ['blank', 'default'];

        $zipFile = new ZipFile();

        foreach ($themeNames as $themeName) {
            $files = $themes[$themeName]->files;

            foreach ($files as $file) {
                $filePath = $file->folder === null ? $file->name : "{$file->folder->value}/$file->name";
                $entry = "prefix/original/$themeName/$filePath";
                $zipFile->addFromString($entry, $file->content);
            }

            $zipFile->saveAsFile(base_path('tests/Unit/__DATA__/Themes/github-themes.zip'));
        }
    }
}
