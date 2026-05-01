<?php

namespace App\Api\Console\Object;

use App\Entity\User;
use App\Service\Route\PermalinkService;

class BlogListObjectFactory
{
    public function __construct(private PermalinkService $permalink) {}

    public function create(User $user): BlogListObject
    {
        return new BlogListObject($user, $this->permalink->getBlogUrl($user->getBlog()));
    }
}
