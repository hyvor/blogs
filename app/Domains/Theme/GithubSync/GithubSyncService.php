<?php declare(strict_types=1);

namespace App\Domains\Theme\GithubSync;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeRepository;
use Exception;
use PhpZip\ZipFile;

/**
 * Downloads all themes from GitHub and updates the database
 */
class GithubSyncService
{
    /**
     * @var array<string, Theme>
     */
    public array $themes = [];

    public static function syncFromGithubZipBall() : void
    {
        $zipBallUrl = 'https://github.com/hyvor/hyvor-blogs-themes/zipball/main';
        $zip = file_get_contents($zipBallUrl);

        if (!$zip) {
            throw new \Exception('Could not download the zipball');
        }

        $syncer = new self($zip);
        $syncer->run();
    }

    public function __construct(private string $zip)
    {
    }

    public function run() : void
    {
        $this->breakIntoThemes();
        $this->saveThemes();
    }

    public function breakIntoThemes() : void
    {
        $zip = new ZipFile();
        $zip->openFromString($this->zip);

        /**
         * First, loop through the zip and break files into Themes
         */
        foreach ($zip as $entry => $content) {

            if (!$entry)
                continue;

            // not interested in directories
            if ($zip->isDirectory($entry)) {
                continue;
            }

            // entry = hyvor-hyvor-blogs-themes-7357d59/original/default/config.def.yaml

            // replace the prefix
            $entry = strval(preg_replace('/^[^\/]+\//', '', $entry));

            preg_match(
                '/(?P<type>[^\/]+)(\/(?P<theme_name>[^\/]+))?(\/(?P<folder>[^\/]+))?(\/(?P<file_name>[^\/]+))?/',
                $entry,
                $matches
            );

            $type = $matches['type'] ?? null;
            $themeName = $matches['theme_name'] ?? null;
            $folder = $matches['folder'] ?? null;
            $fileName = $matches['file_name'] ?? null;

            // fix root files
            if ($folder === 'config.yaml' || $folder === 'config.def.yaml') {
                $fileName = $folder;
                $folder = null;
            }

            // skip unnecessary folders in the repo
            if ($type !== 'original' && $type !== 'ported') {
                continue;
            }

            // only interested in theme files
            if (! $themeName || ! $fileName) {
                continue;
            }

            // folder to enum
            $folder = $folder !== null ? ThemeFileFolderEnum::tryFrom($folder) : null;
            $type = ThemeCreationTypeEnum::from($type);

            // make sure the theme is created
            if (! array_key_exists($themeName, $this->themes)) {
                $this->themes[$themeName] = new Theme($themeName, $type);
            }

            $this->themes[$themeName]->addFile($folder, $fileName, strval($content));
        }
    }

    private function saveThemes() : void
    {
        $latestVersions = Helper::getLatestVersionsOfAllThemes();

        /**
         * Now, loop through each theme and check
         *  if this is a new theme
         *  if the version is changed
         */
        foreach ($this->themes as $theme) {

            // NEW THEME
            if (! array_key_exists($theme->name, $latestVersions)) {
                ThemeRepository::createTheme($theme->name, $theme->type);
            }

            $latestVersion = $latestVersions[$theme->name] ?? null;
            $version = $theme->getVersion();

            if ($version !== $latestVersion) {
                $themeModel = ThemeRepository::getThemeByName($theme->name);

                if (!$themeModel) {
                    throw new Exception('Theme not found: ' . $theme->name);
                }

                $zip = Helper::generateZip($theme);

                ThemeRepository::createThemeVersion(
                    $themeModel,
                    $version,
                    $zip
                );
            }
        }
    }
}
