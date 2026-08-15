<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\HyvorTalkWebsite;
use App\Entity\User;
use App\Service\Blog\BlogService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SyncBlogUsersToWebsiteMessageHandler
{

    public function __construct(
        private BlogService $blogService,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private HyvorTalkService $hyvorTalkService
    ) {}

    public function __invoke(SyncBlogUsersToWebsiteMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if ($blog === null) {
            $this->logger->warning('Blog not found for SyncBlogUsersToWebsiteMessage', ['blogId' => $message->blogId]);
            return;
        }

        if (!$blog->getOrganizationId()) {
            // preview blogs, etc.
            return;
        }

        $hyvorTalk = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        if ($hyvorTalk === null) {
            $this->logger->warning('Hyvor Talk not connected for blog', ['blogId' => $blog->getId()]);
            return;
        }

        if ($message->hyvorUserId === null) {
            $this->syncAllUsers($hyvorTalk);
        } elseif ($message->delete) {
            $this->removeUserFromHyvorTalk($hyvorTalk, $message->hyvorUserId);
        } elseif ($message->role !== null) {
            $this->syncSingleUser($hyvorTalk, $message->hyvorUserId, $message->role);
        }
    }

    private function syncAllUsers(HyvorTalkWebsite $hyvorTalk): void
    {
        $this->logger->info(
            'Syncing all users to Hyvor Talk website',
            ['blogId' => $hyvorTalk->getBlog()->getId(), 'websiteId' => $hyvorTalk->getWebsiteId()]
        );

        // paginate through users and sync them to the website

        $page = 1;
        $pageSize = 25;

        do {
            $users = $this->em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.blog = :blog')
                ->andWhere('u.role IN (:roles)')
                ->andWhere('u.hyvor_user_id IS NOT NULL')
                ->setParameter('blog', $hyvorTalk->getBlog())
                ->setParameter('roles', HyvorTalkService::SYNCED_ROLES)
                ->setFirstResult(($page - 1) * $pageSize)
                ->setMaxResults($pageSize)
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($users as $user) {
                $role = HyvorTalkService::mapUserRole($user->getRole());

                if ($role === null || $user->getHyvorUserId() === null) {
                    continue;
                }

                $this->logger->info('Syncing user to Hyvor Talk website', ['userId' => $user->getId(), 'blogId' => $hyvorTalk->getBlog()->getId()]);

                $this->hyvorTalkService->addMod(
                    $hyvorTalk->getBlog()->getOrganizationId(),
                    $hyvorTalk->getWebsiteId(),
                    $user->getHyvorUserId(),
                    $role
                );
            }

            $page++;
            $this->em->clear();

        } while (count($users) === $pageSize);
    }

    private function syncSingleUser(HyvorTalkWebsite $hyvorTalk, int $hyvorUserId, string $role): void
    {
        $this->logger->info(
            'Syncing single user to Hyvor Talk website',
            ['blogId' => $hyvorTalk->getBlog()->getId(), 'websiteId' => $hyvorTalk->getWebsiteId(), 'hyvorUserId' => $hyvorUserId]
        );

        $this->hyvorTalkService->addMod(
            $hyvorTalk->getBlog()->getOrganizationId(),
            $hyvorTalk->getWebsiteId(),
            $hyvorUserId,
            $role
        );
    }

    private function removeUserFromHyvorTalk(HyvorTalkWebsite $hyvorTalk, int $hyvorUserId): void
    {
        $this->logger->info(
            'Removing mod from Hyvor Talk website',
            ['blogId' => $hyvorTalk->getBlog()->getId(), 'websiteId' => $hyvorTalk->getWebsiteId(), 'hyvorUserId' => $hyvorUserId]
        );

        $this->hyvorTalkService->removeMod(
            $hyvorTalk->getBlog()->getOrganizationId(),
            $hyvorTalk->getWebsiteId(),
            $hyvorUserId
        );
    }

}
