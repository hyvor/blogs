<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $filesystem,
    ) {
    }

    public function getByBlogAndName(Blog $blog, string $name): ?Media
    {
        return $this->em->getRepository(Media::class)->findOneBy([
            'blog' => $blog,
            'name' => $name,
        ]);
    }

    public function getContents(Media $media): ?string
    {
        try {
            return $this->filesystem->read($this->getPath($media));
        } catch (UnableToReadFile) {
            return null;
        }
    }

    private function getPath(Media $media): string
    {
        return 'blog/' . $media->getBlog()->getId() . '/' . $media->getName();
    }
}
