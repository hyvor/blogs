<?php

namespace App\Service\Blog;

use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Enum\BlogType;
use App\Entity\User;
use App\Service\Blog\Seeder\LanguageSeeder;
use App\Service\Blog\Seeder\NavigationSeeder;
use App\Service\Blog\Seeder\RouteSeeder;
use App\Service\Blog\Seeder\TagSeeder;
use App\Service\Blog\Seeder\ThemeSeeder;
use App\Service\Blog\Seeder\UserSeeder;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Symfony\Component\Clock\ClockAwareTrait;

class BlogService
{
    use ClockAwareTrait;

    public const string SUBDOMAIN_REGEX = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i';

    private const FEATURED_IMAGE_URL = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images';

    public function __construct(
        private EntityManagerInterface $em,
        private CommsInterface $comms,
        private LanguageSeeder $languageSeeder,
        private UserSeeder $userSeeder,
        private TagSeeder $tagSeeder,
        private RouteSeeder $routeSeeder,
        private NavigationSeeder $navigationSeeder,
        private ThemeSeeder $themeSeeder,
    ) {}

    public static function isSubdomainReserved(string $subdomain): bool
    {
        return in_array($subdomain, ['new', 'billing', 'select'], true);
    }

    public function getBlogBySubdomain(string $subdomain): ?Blog
    {
        return $this->em->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
    }

    /**
     * Creates a new blog with all default seeded data.
     *
     * Returns the owner User, or null for PREVIEW blogs (which have no owner).
     */
    public function createBlog(
        ?int $hyvorUserId,
        ?int $organizationId,
        string $name,
        string $subdomain,
        BlogType $type = BlogType::DEFAULT,
        ?string $ip = null,
    ): ?User {
        return $this->em->wrapInTransaction(
            function () use ($hyvorUserId, $organizationId, $name, $subdomain, $type, $ip): ?User {
                $blog = new Blog();
                $blog->setHyvorUserId($hyvorUserId);
                $blog->setOrganizationId($organizationId);
                $blog->setIp($ip);
                $blog->setSubdomain($subdomain);
                $blog->setType($type);
                $blog->setTrialEndsAt($this->now()->modify('+14 days'));
                $this->em->persist($blog);
                $this->em->flush();

                if ($organizationId !== null) {
                    $this->comms->send(new ResourceCreated(Component::BLOGS, $organizationId));
                }

                // Seed languages (each language is flushed individually by LanguageService)
                $primaryLanguage = $this->languageSeeder->seed($blog);

                // PREVIEW blogs get a random cover image in their meta
                if ($type === BlogType::PREVIEW) {
                    $blog->setMeta(['cover_url' => self::FEATURED_IMAGE_URL . '/' . rand(1, 20) . '.webp']);
                    $this->em->persist($blog);
                }

                // Create the primary blog variant using the EN language
                $variant = new BlogVariant();
                $variant->setBlog($blog);
                $variant->setBlogId($blog->getId());
                $variant->setLanguage($primaryLanguage);
                $variant->setLanguageId($primaryLanguage->getId());
                $variant->setName($name);
                $this->em->persist($variant);
                $blog->getVariants()->add($variant);

                // Run seeders in dependency order
                $owner = $this->userSeeder->seed($blog);
                $this->tagSeeder->seed($blog);
                $this->routeSeeder->seed($blog);
                $this->navigationSeeder->seed($blog); // queries users/tags, must run after above
                $this->themeSeeder->seed($blog);

                $this->em->flush();

                return $owner;
            },
        );
    }
}
