<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Theme;
use App\Entity\ThemeVersion;
use Doctrine\ORM\EntityManagerInterface;

class ThemeZipService
{
    public function __construct(
        private ThemeFilesService $themeFilesService,
        private EntityManagerInterface $em,
    ) {
    }

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

        $importer = $this->themeFilesService->updateFilesFromZip($blog, $themeVersion->getZip() ?? '');

        if (!$importer->success()) {
            throw new \RuntimeException('Unable to copy the theme');
        }

        $blog->setThemeVersionId($themeVersion->getId());
        $blog->setThemeVersion($themeVersion);
        $this->em->flush();
    }
}
