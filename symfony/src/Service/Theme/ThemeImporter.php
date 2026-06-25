<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;

/**
 * Imports a theme zip into a blog's theme files.
 */
class ThemeImporter
{
    private bool $success = true;

    /**
     * Notes on skipped entries, for debugging.
     *
     * @var string[]
     */
    private array $traces = [];

    public function __construct(
        private Blog $blog,
        private string $zipContent,
        private ThemeFilesService $themeFilesService,
    ) {
    }

    public function import(): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'theme-import-');
        if ($tmpPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the theme import');
        }

        try {
            file_put_contents($tmpPath, $this->zipContent);

            $zip = new \ZipArchive();
            $opened = $zip->open($tmpPath);

            if ($opened !== true) {
                $this->success = false;
                return;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $this->importEntry($zip, $i);
            }

            $zip->close();
        } catch (\Exception) {
            $this->success = false;
        } finally {
            unlink($tmpPath);
        }
    }

    private function importEntry(\ZipArchive $zip, int $index): void
    {
        $entry = $zip->getNameIndex($index);
        if ($entry === false) {
            return;
        }

        $split = explode('/', $entry);

        $folderValue = isset($split[1]) ? $split[0] : null;
        $fileName = isset($split[1]) ? $split[1] : $split[0];

        $folder = null;
        if ($folderValue !== null) {
            $folder = ThemeFileFolder::tryFrom($folderValue);
            if ($folder === null) {
                $this->addTrace("$entry skipped because of invalid folder name ($folderValue)");
                return;
            }
        }

        if (!$this->themeFilesService->isFileAllowedInFolder($folder, $fileName)) {
            $this->addTrace("$entry skipped because it is not allowed");
            return;
        }

        $content = $zip->getFromIndex($index);
        if ($content === false) {
            $this->addTrace("$entry skipped because it has no content");
            return;
        }

        $this->themeFilesService->createOrUpdateFile($this->blog, $folder, $fileName, $content);
    }

    private function addTrace(string $trace): void
    {
        $this->traces[] = $trace;
    }

    /**
     * @return string[]
     */
    public function traces(): array
    {
        return $this->traces;
    }

    public function success(): bool
    {
        return $this->success;
    }
}
