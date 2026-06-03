<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use Doctrine\ORM\EntityManagerInterface;

class ThemeFilesService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getFile(Blog $blog, string $name, ?ThemeFileFolder $folder): ?ThemeFile
    {
        return $this->em->getRepository(ThemeFile::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'folder' => $folder?->value,
            'name' => $name,
        ]);
    }

    /** @return ThemeFile[] */
    public function getFilesInFolder(Blog $blog, ThemeFileFolder $folder): array
    {
        return $this->em->getRepository(ThemeFile::class)->findBy([
            'blog_id' => $blog->getId(),
            'folder' => $folder->value,
        ]);
    }
}
