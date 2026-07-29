<?php

namespace App\Tests\Fake;

use App\Entity\Blog;
use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Replaces HyvorPostService in tests that need to exercise code that calls out
 * to Hyvor Post's API (which real HyvorPostService would do over HTTP via the
 * SDK). Register it with `$container->set(HyvorPostService::class, $fake)`.
 *
 * Does not call parent::__construct(), so it never touches the real service's
 * (private) CloudApiService-backed methods. Only the methods overridden below
 * are safe to call on an instance of this class.
 */
final class HyvorPostServiceFake extends HyvorPostService
{
    public ?string $lastConnectName = null;
    public ?string $lastConnectSubdomain = null;

    /** @var array<int, array{newsletterId: int, hyvorUserId: int}> */
    public array $addedUsers = [];

    /** @var array<int, array{newsletterId: int, hyvorUserId: int}> */
    public array $removedUsers = [];

    public ?\Throwable $throwOnAddUser = null;
    public ?\Throwable $throwOnRemoveUser = null;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[\Override]
    public function getHyvorPostOfBlog(Blog $blog): ?HyvorPost
    {
        return $this->em->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
    }

    #[\Override]
    public function connect(Blog $blog, string $name, string $subdomain): HyvorPost
    {
        $this->lastConnectName = $name;
        $this->lastConnectSubdomain = $subdomain;

        $now = new \DateTimeImmutable();

        $hyvorPost = new HyvorPost();
        $hyvorPost->setBlog($blog);
        $hyvorPost->setNewsletterId(random_int(1, 1_000_000));
        $hyvorPost->setCreatedByBlogs(true);
        $hyvorPost->setCreatedAt($now);
        $hyvorPost->setUpdatedAt($now);

        $this->em->persist($hyvorPost);
        $this->em->flush();

        return $hyvorPost;
    }

    #[\Override]
    public function addUser(HyvorPost $hyvorPost, int $hyvorUserId): void
    {
        if ($this->throwOnAddUser !== null) {
            throw $this->throwOnAddUser;
        }

        $this->addedUsers[] = ['newsletterId' => $hyvorPost->getNewsletterId(), 'hyvorUserId' => $hyvorUserId];
    }

    #[\Override]
    public function removeUser(HyvorPost $hyvorPost, int $hyvorUserId): void
    {
        if ($this->throwOnRemoveUser !== null) {
            throw $this->throwOnRemoveUser;
        }

        $this->removedUsers[] = ['newsletterId' => $hyvorPost->getNewsletterId(), 'hyvorUserId' => $hyvorUserId];
    }
}
