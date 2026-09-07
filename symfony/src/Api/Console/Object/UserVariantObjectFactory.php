<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Route\PermalinkService;

class UserVariantObjectFactory
{
    public function __construct(private PermalinkService $permalink) {}

    public function create(UserVariant $variant, User $user, Blog $blog): UserVariantObject
    {
        $url = $this->permalink->getAuthorPermalink($user, $blog, $variant->getLanguage());

        return new UserVariantObject($variant, $url);
    }
}
