<?php

namespace App\Service\User\Comms;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;

#[AsEventListener]
class MemberRemovedListener
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserService $userService,
    ) {}

    public function __invoke(MemberRemoved $event): void
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('u.blog', 'b')
            ->andWhere('b.organization_id = :orgId')
            ->andWhere('u.id = :userId')
            ->setParameter('orgId', $event->getOrganizationId())
            ->setParameter('userId', $event->getUserId())
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $this->userService->deleteUser($user);
        }
    }

}
