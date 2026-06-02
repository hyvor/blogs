<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\ThemeFile;
use Doctrine\ORM\EntityManagerInterface;

class ThemeFilesService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getFile(Blog $blog, string $name, ?string $folder): ?ThemeFile
    {
        return $this->em->getRepository(ThemeFile::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'folder' => $folder,
            'name' => $name,
        ]);
    }

    /** @return ThemeFile[] */
    public function getFilesInFolder(Blog $blog, string $folder): array
    {
        return $this->em->getRepository(ThemeFile::class)->findBy([
            'blog_id' => $blog->getId(),
            'folder' => $folder,
        ]);
    }
}
