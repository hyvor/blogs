<?php

namespace App\Api\Console\Object\PostList;

use App\Entity\Language;
use App\Entity\Post;
use App\Service\Route\PermalinkService;

class PostListObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    public function create(Post $post, Language $language): PostListObject
    {
        return new PostListObject($post, $language, $this->permalinkService);
    }
}
