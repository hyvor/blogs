<?php

namespace App\Service\Post\PostAuthor;

use App\Entity\User;
use Doctrine\DBAL\Connection;

class PostAuthorService
{

    public function __construct(
        private Connection $connection,
    ) {}

    public function deleteByUser(User $user): void
    {
        $this->connection->executeStatement(
            'DELETE FROM post_author WHERE user_id = ?',
            [$user->getId()],
        );
    }

}
