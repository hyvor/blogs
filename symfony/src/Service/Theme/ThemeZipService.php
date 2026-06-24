<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Theme;
use App\Entity\ThemeVersion;
use Doctrine\ORM\EntityManagerInterface;

class ThemeZipService
{
    public function __construct(
        private ThemeFilesService $themeFilesService,
        private EntityManagerInterface $em,
    ) {}

    public function exportZip(Blog $blog): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'theme-export-');
        if ($tmpPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the theme export');
        }

        try {
            $zip = new \ZipArchive();
            $zip->open($tmpPath, \ZipArchive::OVERWRITE);

            foreach ($this->themeFilesService->getAllFilesOfBlog($blog) as $file) {
                $folder = $file->getFolder();
                $entryName = $folder === null ? $file->getName() : $folder->value . '/' . $file->getName();
                $zip->addFromString($entryName, $file->getContent() ?? '');
            }

            $zip->close();

            $content = file_get_contents($tmpPath);
            if ($content === false) {
                throw new \RuntimeException('Unable to read the generated theme zip');
            }

            return $content;
        } finally {
            unlink($tmpPath);
        }
    }

    /**
     * Imports a zip into a blog. All existing theme files are deleted first.
     */
    public function importZip(Blog $blog, string $zipContent): bool
    {
        $this->themeFilesService->deleteAllFiles($blog);

        $tmpPath = tempnam(sys_get_temp_dir(), 'theme-import-');
        if ($tmpPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the theme import');
        }

        try {
            file_put_contents($tmpPath, $zipContent);

            $zip = new \ZipArchive();
            $opened = $zip->open($tmpPath);

            if ($opened !== true) {
                return false;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if ($entry === false) {
                    continue;
                }

                $split = explode('/', $entry);

                $folderValue = isset($split[1]) ? $split[0] : null;
                $fileName = isset($split[1]) ? $split[1] : $split[0];

                $folder = null;
                if ($folderValue !== null) {
                    $folder = ThemeFileFolder::tryFrom($folderValue);
                    if ($folder === null) {
                        // invalid folder
                        continue;
                    }
                }

                if (!$this->fileAllowed($folder, $fileName)) {
                    continue;
                }

                $content = $zip->getFromIndex($i);
                if ($content === false) {
                    continue;
                }

                $this->themeFilesService->createOrUpdateFile($blog, $folder, $fileName, $content);
            }

            $zip->close();

            return true;
        } catch (\Exception) {
            return false;
        } finally {
            unlink($tmpPath);
        }
    }

    private function fileAllowed(?ThemeFileFolder $folder, string $fileName): bool
    {
        return match ($folder) {
            null => in_array($fileName, ['config.yaml', 'config.def.yaml'], true),
            ThemeFileFolder::TEMPLATES => str_ends_with($fileName, '.twig'),
            ThemeFileFolder::LANG => str_ends_with($fileName, '.yaml'),
            ThemeFileFolder::STYLES => str_ends_with($fileName, '.scss'),
            ThemeFileFolder::ASSETS => true,
        };
    }

    /**
     * Copies the latest version of a global theme onto a blog.
     */
    public function copyThemeToBlog(Blog $blog, string $themeName): void
    {
        $theme = $this->em->getRepository(Theme::class)->findOneBy(['name' => $themeName]);

        if ($theme === null) {
            return;
        }

        /** @var ThemeVersion|null $themeVersion */
        $themeVersion = $this->em->getRepository(ThemeVersion::class)->findOneBy(
            ['theme' => $theme],
            ['id' => 'DESC'],
        );

        if ($themeVersion === null) {
            throw new \RuntimeException('Theme version not found');
        }

        $success = $this->importZip($blog, $themeVersion->getZip() ?? '');

        if (!$success) {
            throw new \RuntimeException('Unable to copy the theme');
        }

        $blog->setThemeVersionId($themeVersion->getId());
        $blog->setThemeVersion($themeVersion);
        $this->em->flush();
    }
}
