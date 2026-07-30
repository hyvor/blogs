<?php

namespace App\Service\Integration\HyvorPost;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Service\Blog\BlogService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SyncBlogUsersToNewsletterMessageHandler
{

    public function __construct(
        private BlogService $blogService,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private HyvorPostService $hyvorPostService
    ) {}

    public function __invoke(SyncBlogUsersToNewsletterMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if ($blog === null) {
            $this->logger->warning('Blog not found for SyncBlogUsersToNewsletterMessage', ['blogId' => $message->blogId]);
            return;
        }

        if (!$blog->getOrganizationId()) {
            // preview blogs, etc.
            return;
        }

        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($blog);

        if ($hyvorPost === null) {
            $this->logger->warning('Hyvor Post not connected for blog', ['blogId' => $blog->getId()]);
            return;
        }

        // paginate through users and sync them to the newsletter

        $page = 1;
        $pageSize = 25;

        do {
            $users = $this->em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.blog = :blog')
                ->andWhere('u.role IN (:roles)')
                ->setParameter('blog', $blog)
                ->setParameter('roles', HyvorPostService::SYNCED_ROLES)
                ->setFirstResult(($page - 1) * $pageSize)
                ->setMaxResults($pageSize)
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($users as $user) {
                $this->logger->info('Syncing user to newsletter', ['userId' => $user->getId(), 'blogId' => $blog->getId()]);

                $this->hyvorPostService->addUser(
                    $blog->getOrganizationId(),
                    $hyvorPost->getNewsletterId(),
                    $user->getHyvorUserId()
                );
            }

            $page++;
            $this->em->clear();

        } while (count($users) === $pageSize);

    }

}
