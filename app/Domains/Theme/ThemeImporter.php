<?php

namespace App\Domains\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use ValueError;

class ThemeImporter
{

    private bool $success = true;

    /**
     * Take a note on skipped files for debugging
     *
     * @var string[]
     */
    private array $skipTraces = [];

    public function __construct(private Blog $blog, private string $zipContent) {}

    public function import()
    {

        try {

            $zip = new ZipFile();
            $zip->openFromString($this->zipContent);

            foreach ($zip as $entry => $content) {

                // $entry = assets/image.svg

                $split = explode('/', $entry);

                $folder = isset($split[1]) ? $split[0] : null;
                $fileName = isset($split[1]) ? $split[1] : $split[0];

                if ($folder !== null) {

                    try {
                        $folder = ThemeFileFolderEnum::from($folder);
                    } catch (ValueError) {
                        // invalid folder
                        $this->addTrace("$fileName skipped because of invalid folder name ($folder)");
                        continue;
                    }

                }

                if (!$this->fileAllowed($folder, $fileName)) {
                    $this->addTrace("$entry skipped because it is not allowed");
                    continue;
                }

                ThemeFilesRepository::createOrUpdateFile($this->blog, $folder, $fileName, $content);

            }

        } catch (ZipException) {
            $this->success = false;
        }

    }

    private function fileAllowed(?ThemeFileFolderEnum $folder, string $fileName) : bool
    {

        if ($folder === null) {
            return in_array($fileName, ['config.yaml', 'config.def.yaml']);
        } else if ($folder === ThemeFileFolderEnum::TEMPLATES) {
            return str_ends_with($fileName, '.twig');
        } else if ($folder === ThemeFileFolderEnum::LANG) {
            return str_ends_with($fileName, '.yaml');
        } else if ($folder === ThemeFileFolderEnum::STYLES) {
            return str_ends_with($fileName, '.scss');
        } else if ($folder === ThemeFileFolderEnum::ASSETS) {
            return true;
        }

        return false;

    }

    private function addTrace(string $skipTrace)
    {
        $this->skipTraces[] = $skipTrace;
    }

    public function skipTraces()
    {
        return $this->skipTraces;
    }

    public function success() {
        return $this->success;
    }

}