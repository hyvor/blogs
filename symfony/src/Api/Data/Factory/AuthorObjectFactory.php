<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\AuthorObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\User;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class AuthorObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    public function create(User $user, Blog $blog, Language $language): AuthorObject
    {
        return new AuthorObject($user, $blog, $language, $this->permalinkService);
    }
}
