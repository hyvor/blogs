<?php

namespace App\Service\Blog;

use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class BlogService
{
    use ClockAwareTrait;

    public const string SUBDOMAIN_REGEX = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i';

    // private const FEATURED_IMAGE_URL = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images';

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function isSubdomainReserved(string $subdomain): bool
    {
        return in_array($subdomain, ['new', 'billing', 'select'], true);
    }

    public function getBlogBySubdomain(string $subdomain): ?Blog
    {
        return $this->em->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
    }
}
