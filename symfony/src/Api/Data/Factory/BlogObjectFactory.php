<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\BlogObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Service\Route\PermalinkService;

class BlogObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    public function create(Blog $blog, Language $language): BlogObject
    {
        return new BlogObject($blog, $language, $this->permalinkService);
    }
}
