<?php

namespace App\Domains\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use ValueError;

// imports a theme into a blog, using a zip
class ThemeImporter
{
    private bool $success = true;

    /**
     * Take a note on skipped files for debugging
     *
     * @var string[]
     */
    private array $skipTraces = [];

    public function __construct(private Blog $blog, private string $zipContent)
    {
    }

    public function import() : void
    {
        try {
            $zip = new ZipFile();
            $zip->openFromString($this->zipContent);

            foreach ($zip as $entry => $content) {
                // $entry = assets/image.svg

                $split = explode('/', $entry ? $entry : '');

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

                if (! $this->fileAllowed($folder, $fileName)) {
                    $this->addTrace("$entry skipped because it is not allowed");

                    continue;
                }

                if ($content === null) {
                    $this->addTrace("$entry skipped because it has no content");
                    continue;
                }
                ThemeFilesRepository::createOrUpdateFile($this->blog, $folder, $fileName, $content);
            }
        } catch (ZipException $e) {
            $this->success = false;
        }
    }

    private function fileAllowed(?ThemeFileFolderEnum $folder, string $fileName): bool
    {
        if ($folder === null) {
            return in_array($fileName, ['config.yaml', 'config.def.yaml']);
        } elseif ($folder === ThemeFileFolderEnum::TEMPLATES) {
            return str_ends_with($fileName, '.twig');
        } elseif ($folder === ThemeFileFolderEnum::LANG) {
            return str_ends_with($fileName, '.yaml');
        } elseif ($folder === ThemeFileFolderEnum::STYLES) {
            return str_ends_with($fileName, '.scss');
        } elseif ($folder === ThemeFileFolderEnum::ASSETS) {
            return true;
        }
    }

    private function addTrace(string $skipTrace) : void
    {
        $this->skipTraces[] = $skipTrace;
    }

    /**
     * @return string[]
     */
    public function skipTraces() : array
    {
        return $this->skipTraces;
    }

    public function success() : bool
    {
        return $this->success;
    }
}
