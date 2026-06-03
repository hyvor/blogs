<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire(param: 'app.storage_path')]
        private string $storagePath = '',
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
        $path = $this->storagePath . '/blog/' . $media->getBlog()->getId() . '/' . $media->getName();
        return file_exists($path) ? (string) file_get_contents($path) : null;
    }
}
