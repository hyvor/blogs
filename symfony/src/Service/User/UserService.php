<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\Post\PostAuthor\PostAuthorService;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{

    public function __construct(
        private EntityManagerInterface $em,
        private PostAuthorService $postAuthorService,
    ) {}

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
