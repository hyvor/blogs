<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use App\Entity\ThemeVersion;
use App\Service\Theme\Event\AssetEditedEvent;
use App\Service\Theme\Event\ConfigEditedEvent;
use App\Service\Theme\Event\LangEditedEvent;
use App\Service\Theme\Event\StylesEditedEvent;
use App\Service\Theme\Event\TemplateEditedEvent;
use App\Service\Theme\Exception\ThemeExportException;
use App\Service\Theme\Exception\ThemeImportException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ThemeFilesService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $ed,
    ) {
    }

    public function getFile(Blog $blog, string $name, ?ThemeFileFolder $folder): ?ThemeFile
    {
        return $this->em->getRepository(ThemeFile::class)->findOneBy([
            'blog' => $blog,
            'folder' => $folder,
            'name' => $name,
        ]);
    }

    /** @return ThemeFile[] */
    public function getAllFilesOfBlog(Blog $blog): array
    {
        return $this->em->getRepository(ThemeFile::class)->findBy(['blog' => $blog]);
    }

    public function createOrUpdateFile(
        Blog $blog,
        ?ThemeFileFolder $folder,
        string $name,
        ?string $content,
        bool $event = true,
    ): ThemeFile {
        $file = $this->getFile($blog, $name, $folder);
        $now = $this->now();

        if ($file === null) {
            $file = new ThemeFile();
            $file->setBlog($blog);
            $file->setFolder($folder);
            $file->setName($name);
            $file->setCreatedAt($now);
        }

        $file->setContent($content);
        $file->setUpdatedAt($now);

        $this->em->persist($file);
        $this->em->flush();

        if ($event) {
            $this->dispatchFileUpdatedEvent($file);
        }

        return $file;
    }

    /**
     * @param array{name?: ?string, content?: ?string} $updates
     */
    public function updateFile(ThemeFile $file, array $updates): ThemeFile
    {
        if (array_key_exists('name', $updates) && $updates['name'] !== null) {
            $file->setName($updates['name']);
        }
        if (array_key_exists('content', $updates)) {
            $content = $updates['content'];
            $file->setContent($content === '' ? null : $content);
        }
        $file->setUpdatedAt($this->now());

        $this->em->flush();

        $this->dispatchFileUpdatedEvent($file);

        return $file;
    }

    public function deleteFile(ThemeFile $file): void
    {
        $this->em->remove($file);
        $this->em->flush();
    }

    public function deleteAllFiles(Blog $blog): void
    {
        foreach ($this->getAllFilesOfBlog($blog) as $file) {
            $this->em->remove($file);
        }
        $this->em->flush();
    }

    private function dispatchFileUpdatedEvent(ThemeFile $file): void
    {
        $blog = $file->getBlog();

        match ($file->getFolder()) {
            ThemeFileFolder::STYLES => $this->ed->dispatch(new StylesEditedEvent($blog)),
            ThemeFileFolder::ASSETS => $this->ed->dispatch(new AssetEditedEvent($blog, $file->getName())),
            ThemeFileFolder::TEMPLATES => $this->ed->dispatch(new TemplateEditedEvent($file)),
            ThemeFileFolder::LANG => $this->ed->dispatch(new LangEditedEvent($file)),
            null => $file->getName() === 'config.yaml' ? $this->ed->dispatch(new ConfigEditedEvent($file)) : null,
        };
    }

    /**
     * @param string[] $names
     * @return ThemeFile[]
     */
    public function getFilesByNames(Blog $blog, array $names, ?ThemeFileFolder $folder): array
    {
        return $this->em->getRepository(ThemeFile::class)->findBy([
            'blog' => $blog,
            'folder' => $folder,
            'name' => $names,
        ]);
    }

    /** @return ThemeFile[] */
    public function getFilesInFolder(Blog $blog, ThemeFileFolder $folder): array
    {
        return $this->em->getRepository(ThemeFile::class)->findBy([
            'blog' => $blog,
            'folder' => $folder,
        ]);
    }

    /**
     * @throws ThemeImportException
     */
    public function updateFilesFromZip(Blog $blog, string $zipContent): ThemeImporter
    {
        return $this->em->wrapInTransaction(function () use ($blog, $zipContent) {
            $this->deleteAllFiles($blog);

            $importer = new ThemeImporter($blog, $zipContent, $this);
            $importer->import();

            return $importer;
        });
    }

    /**
     * @throws ThemeImportException
     */
    public function updateFilesFromThemeVersion(Blog $blog, ThemeVersion $version): ThemeImporter
    {
        return $this->em->wrapInTransaction(function () use ($blog, $version) {
            $importer = $this->updateFilesFromZip($blog, $version->getZip() ?? '');

            $blog->setThemeVersion($version);
            $blog->setUpdatedAt($this->now());
            $this->em->flush();

            return $importer;
        });
    }

    public function isFileAllowedInFolder(?ThemeFileFolder $folder, string $fileName): bool
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
     * @throws ThemeExportException known errors
     * @throws \RuntimeException for unexpected errors
     */
    public function exportFilesToZip(Blog $blog): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'theme-export-');
        if ($tmpPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the theme export');
        }

        try {
            $zip = new \ZipArchive();
            $zip->open($tmpPath, \ZipArchive::OVERWRITE);

            $files = $this->getAllFilesOfBlog($blog);

            if (count($files) === 0) {
                throw new ThemeExportException('No theme files found to export');
            }

            foreach ($files as $file) {
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
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

}
