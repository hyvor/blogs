<?php

namespace App\Service\User;

use App\Entity\Blog;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Post\PostAuthor\PostAuthorService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;

class UserService
{

    public function __construct(
        private EntityManagerInterface $em,
        private PostAuthorService $postAuthorService,
        private UserRepository $userRepository,
    ) {}

    public function getUserByBlogAndAuthUser(Blog $blog, AuthUser|int $authUserOrId): ?User
    {
        $authUserId = $authUserOrId instanceof AuthUser ? $authUserOrId->id : $authUserOrId;

        /** @var User|null */
        return $this->userRepository->findOneBy([
            'blog' => $blog,
            'hyvor_user_id' => $authUserId,
            'status' => 'active',
        ]);
    }

    /**
     * @return User[]
     */
    public function getBlogsForUser(int $hyvorUserId, int $organizationId): array
    {
        /** @var User[] */
        return $this->userRepository
            ->createQueryBuilder('u')
            ->join('u.blog', 'b')
            ->leftJoin('b.variants', 'bv')
            ->addSelect('b', 'bv')
            ->where('u.hyvor_user_id = :userId')
            ->andWhere('b.organization_id = :orgId')
            ->andWhere('u.status = :status')
            ->setParameter('userId', $hyvorUserId)
            ->setParameter('orgId', $organizationId)
            ->setParameter('status', 'active')
            ->orderBy('u.sort', 'ASC')
            ->addOrderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $blogIds ordered list of blog IDs to set sort order
     */
    public function changeBlogSorts(int $hyvorUserId, array $blogIds): void
    {
        $i = 1;
        foreach ($blogIds as $blogId) {
            $this->em->getConnection()->executeStatement(
                'UPDATE users SET sort = ? WHERE blog_id = ? AND hyvor_user_id = ?',
                [$i, $blogId, $hyvorUserId],
            );
            $i++;
        }
    }

    /**
     * Note: this does not emit events like Laravel does, which means
     * counts are not updated and webhooks are not triggered
     */
    public function deleteUser(User $user): void
    {
        $this->em->wrapInTransaction(function () use ($user) {
            foreach ($user->getVariants() as $variant) {
                $this->em->remove($variant);
            }

            $this->postAuthorService->deleteByUser($user);

            $this->em->remove($user);
        });
    }
}
