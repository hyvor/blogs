<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\Exception\ThemeImportException;

/**
 * Imports a theme zip into a blog's theme files.
 */
class ThemeImporter
{

    /**
     * Notes on skipped entries, for debugging.
     *
     * @var string[]
     */
    private array $logs = [];

    public function __construct(
        private Blog $blog,
        private string $zipContent,
        private ThemeFilesService $themeFilesService,
    ) {
    }

    /**
     * @throws ThemeImportException
     */
    public function import(): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'theme-import-');
        if ($tmpPath === false) {
            throw new ThemeImportException('Unable to create a temporary file for the theme import');
        }

        if (file_put_contents($tmpPath, $this->zipContent) === false) {
            throw new ThemeImportException('Unable to write the theme zip content to the temporary file');
        }

        $zip = new \ZipArchive();
        $opened = $zip->open($tmpPath);

        if ($opened !== true) {
            throw new ThemeImportException('Unable to open the zip file for the theme import');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $this->importEntry($zip, $i);
        }

        $zip->close();
        unlink($tmpPath);
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
            $folderName = $folder === null ? 'root' : $folder->value;
            $this->addTrace("$entry skipped because it is not allowed in the $folderName folder");
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
        $this->logs[] = $trace;
    }

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
