<?php

namespace App\Service\Post\PostAuthor;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PostAuthorService
{

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function deleteByUser(User $user): void
    {
        $this->em
            ->getRepository(\App\Entity\PostAuthor::class)
            ->createQueryBuilder('pa')
            ->andWhere('pa.user = :user')
            ->setParameter('user', $user)
            ->delete()
            ->getQuery()
            ->execute();
    }

}
