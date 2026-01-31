<?php

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;

#[AsEventListener]
class MemberRemovedListener
{

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(MemberRemoved $event): void
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('user.blog', 'b')
            ->andWhere('b.organization_id = :orgId')
            ->andWhere('u.id = :userId')
            ->setParameter('orgId', $event->getOrganizationId())
            ->setParameter('userId', $event->getUserId())
            ->getQuery()
            ->getResult();

        dd($users);
    }

}
