<?php

namespace App\Service\Theme\RepoSync;

use App\Entity\Enum\ThemeCreationType;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\RepoSync\Exception\RepoSyncException;
use App\Service\Theme\ThemeService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZipArchive;

/**
 * Downloads all themes from the GitHub repo and syncs them to the database.
 */
class RepoSyncService
{
    private const string REPO_ZIP_URL = 'https://github.com/hyvor/hyvor-blogs-themes/zipball/main';

    /** @var array<string, ThemeData> */
    public array $themes = [];

    public function __construct(
        private ThemeService $themeService,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @throws RepoSyncException
     */
    public function downloadAndSync(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'themes_zip_');

        try {
            $this->downloadToFile(self::REPO_ZIP_URL, $tmpFile);
            $this->syncFromFile($tmpFile);
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * @throws RepoSyncException
     */
    private function downloadToFile(string $url, string $destination): void
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            $fileHandle = fopen($destination, 'w');

            if ($fileHandle === false) {
                throw new RepoSyncException('Could not open temp file for writing');
            }

            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($fileHandle, $chunk->getContent());
            }
        } catch (TransportExceptionInterface $e) {
            throw new RepoSyncException('Error downloading zip file: ' . $e->getMessage(), 0, $e);
        } finally {
            if (isset($fileHandle) && is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }

    /**
     * @throws RepoSyncException
     */
    public function syncFromFile(string $zipFilePath): void
    {
        $this->breakIntoThemes($zipFilePath);
        $this->saveThemes();
    }

    /**
     * @throws RepoSyncException
     */
    public function breakIntoThemes(string $zipFilePath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath) !== true) {
            throw new RepoSyncException('Could not open zip file: ' . $zipFilePath);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            // not interested in directories
            if ($entry === false || str_ends_with($entry, '/')) {
                continue;
            }

            // entry = hyvor-hyvor-blogs-themes-7357d59/original/default/config.def.yaml

            // Strip leading prefix: hyvor-hyvor-blogs-themes-abc123/...
            $entry = (string) preg_replace('/^[^\/]+\//', '', $entry);

            preg_match(
                '/(?P<type>[^\/]+)(\/(?P<theme_name>[^\/]+))?(\/(?P<folder>[^\/]+))?(\/(?P<file_name>[^\/]+))?/',
                $entry,
                $matches
            );

            $type = $matches['type'] ?? null;
            $themeName = $matches['theme_name'] ?? null;
            $folder = $matches['folder'] ?? null;
            $fileName = $matches['file_name'] ?? null;

            // Root-level config files: original/hello/config.yaml (no sub-folder)
            if ($folder === 'config.yaml' || $folder === 'config.def.yaml') {
                $fileName = $folder;
                $folder = null;
            }

            // skip unnecessary folders in the repo
            if ($type !== 'original' && $type !== 'ported') {
                continue;
            }

            // only interested in theme files
            if (!$themeName || !$fileName) {
                continue;
            }

            $folderEnum = $folder !== null ? ThemeFileFolder::tryFrom($folder) : null;
            $typeEnum = ThemeCreationType::from($type);
            $content = (string) $zip->getFromIndex($i);

            // make sure the theme is created
            if (!array_key_exists($themeName, $this->themes)) {
                $this->themes[$themeName] = new ThemeData($themeName, $typeEnum);
            }

            $this->themes[$themeName]->addFile($folderEnum, $fileName, $content);
        }

        $zip->close();
    }

    /**
     * @throws RepoSyncException
     */
    private function saveThemes(): void
    {
        $latestVersions = $this->themeService->getLatestVersionsOfAllThemes();

        foreach ($this->themes as $theme) {
            if (!array_key_exists($theme->name, $latestVersions)) {
                $this->themeService->createTheme($theme->name, $theme->type);
            }

            $latestVersion = $latestVersions[$theme->name] ?? null;
            $version = $theme->getVersion();

            if ($version !== $latestVersion) {
                $themeModel = $this->themeService->getThemeByName($theme->name);

                if ($themeModel === null) {
                    throw new RepoSyncException('Theme not found after creation: ' . $theme->name);
                }

                $zip = $this->generateZip($theme);
                $this->themeService->createThemeVersion($themeModel, $version, $zip);
            }
        }
    }

    /** @throws RepoSyncException */
    private function generateZip(ThemeData $theme): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'theme_out_');

        try {
            $zip = new ZipArchive();
            $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($theme->files as $file) {
                $fileName = $file->folder === null
                    ? $file->name
                    : "{$file->folder->value}/{$file->name}";
                $zip->addFromString($fileName, $file->content);
            }

            $zip->close();

            $content = file_get_contents($tmpFile);

            if ($content === false) {
                throw new RepoSyncException('Could not read generated zip file');
            }

            return $content;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
