<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire(param: 'app.storage_path')]
        private string $storagePath = '',
    ) {}

    public function getByBlogAndName(int $blogId, string $name): ?Media
    {
        return $this->em->getRepository(Media::class)->findOneBy([
            'blog_id' => $blogId,
            'name' => $name,
        ]);
    }

    public function getContents(Media $media): ?string
    {
        $path = $this->storagePath . '/blog/' . $media->getBlogId() . '/' . $media->getName();
        return file_exists($path) ? (string)file_get_contents($path) : null;
    }
}
