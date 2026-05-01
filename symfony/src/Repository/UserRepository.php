<?php

namespace App\Repository;

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
    public function getBlogsForUser(int $hyvorUserId, int $organizationId): array
    {
        return $this->createQueryBuilder('u')
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
}
