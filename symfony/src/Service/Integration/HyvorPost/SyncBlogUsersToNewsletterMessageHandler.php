<?php

namespace App\Service\Integration\HyvorPost;

use App\Entity\HyvorPost;
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

        if ($message->hyvorUserId === null) {
            $this->syncAllUsers($hyvorPost);
        } elseif ($message->delete) {
            $this->deleteUserFromHyvorPost($hyvorPost, $message->hyvorUserId);
        } else {
            $this->syncSingleUser($hyvorPost, $message->hyvorUserId);
        }
    }

    private function syncAllUsers(HyvorPost $hyvorPost): void
    {
        $this->logger->info(
            'Syncing all users to newsletter',
            ['blogId' => $hyvorPost->getBlog()->getId(), 'newsletterId' => $hyvorPost->getNewsletterId()]
        );

        $organizationId = $hyvorPost->getBlog()->getOrganizationId();
        if ($organizationId === null) {
            // preview blogs, etc.
            return;
        }

        // paginate through users and sync them to the newsletter
        $page = 1;
        $pageSize = 25;

        do {
            /** @var User[] $users */
            $users = $this->em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.blog = :blog')
                ->andWhere('u.role IN (:roles)')
                ->setParameter('blog', $hyvorPost->getBlog())
                ->setParameter('roles', HyvorPostService::SYNCED_ROLES)
                ->setFirstResult(($page - 1) * $pageSize)
                ->setMaxResults($pageSize)
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($users as $user) {
                $hyvorUserId = $user->getHyvorUserId();
                if ($hyvorUserId === null) {
                    // guests
                    continue;
                }

                $this->logger->info('Syncing user to newsletter', ['userId' => $user->getId(), 'blogId' => $hyvorPost->getBlog()->getId()]);

                $this->hyvorPostService->addUser(
                    $organizationId,
                    $hyvorPost->getNewsletterId(),
                    $hyvorUserId
                );
            }

            $page++;
            $this->em->clear();

        } while (count($users) === $pageSize);
    }

    private function syncSingleUser(HyvorPost $hyvorPost, int $hyvorUserId): void
    {
        $organizationId = $hyvorPost->getBlog()->getOrganizationId();
        if ($organizationId === null) {
            return;
        }

        $this->logger->info(
            'Syncing single user to newsletter',
            ['blogId' => $hyvorPost->getBlog()->getId(), 'newsletterId' => $hyvorPost->getNewsletterId(), 'hyvorUserId' => $hyvorUserId]
        );

        $this->hyvorPostService->addUser(
            $organizationId,
            $hyvorPost->getNewsletterId(),
            $hyvorUserId
        );
    }

    private function deleteUserFromHyvorPost(HyvorPost $hyvorPost, int $hyvorUserId): void
    {
        $organizationId = $hyvorPost->getBlog()->getOrganizationId();
        if ($organizationId === null) {
            return;
        }

        $this->logger->info(
            'Deleting user from newsletter',
            ['blogId' => $hyvorPost->getBlog()->getId(), 'newsletterId' => $hyvorPost->getNewsletterId(), 'hyvorUserId' => $hyvorUserId]
        );

        $this->hyvorPostService->removeUser(
            $organizationId,
            $hyvorPost->getNewsletterId(),
            $hyvorUserId
        );
    }

}
