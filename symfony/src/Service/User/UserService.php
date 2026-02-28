<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Post\PostAuthor\PostAuthorService;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{

    public function __construct(
        private EntityManagerInterface $em,
        private PostAuthorService $postAuthorService,
        private UserRepository $userRepository,
    ) {}

    /**
     * @return User[]
     */
    public function getBlogsForUser(int $hyvorUserId, int $organizationId): array
    {
        return $this->userRepository->createQueryBuilder('u')
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
