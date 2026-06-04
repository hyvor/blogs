<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\AuthorObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\User;
use App\Service\Route\PermalinkService;

class AuthorObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    /**
     * @param array<array{language: Language, name: ?string, bio: ?string, location: ?string}> $variantData
     */
    public function create(User $user, Blog $blog, Language $language, array $variantData = []): AuthorObject
    {
        return new AuthorObject($user, $blog, $language, $this->permalinkService, $variantData);
    }
}
