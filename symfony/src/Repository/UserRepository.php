<?php

namespace App\Repository;

use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findAdmins(Blog $blog): array
    {
        /** @var User[] */
        return $this->createQueryBuilder('u')
            ->where('u.blog = :blog')
            ->andWhere('u.role = :role')
            ->andWhere('u.status = :status')
            ->andWhere('u.email IS NOT NULL')
            ->setParameter('blog', $blog)
            ->setParameter('role', UserRole::ADMIN)
            ->setParameter('status', UserStatus::ACTIVE)
            ->getQuery()
            ->getResult();
    }
}
