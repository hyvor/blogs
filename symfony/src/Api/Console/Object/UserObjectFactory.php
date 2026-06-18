<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\User;

class UserObjectFactory
{
    public function __construct(private UserVariantObjectFactory $variantFactory) {}

    public function create(User $user, Blog $blog): UserObject
    {
        $variants = $user->getVariants()->toArray();
        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());

        $variantObjects = array_map(
            fn($variant) => $this->variantFactory->create($variant, $user, $blog),
            $variants,
        );

        return new UserObject($user, $variantObjects);
    }
}
