<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\BlogObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Service\Route\PermalinkService;

class BlogObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    /**
     * @param Navigation[] $navigations
     * @param Language[] $allLanguages
     */
    public function create(Blog $blog, Language $language, array $navigations = [], array $allLanguages = []): BlogObject
    {
        return new BlogObject($blog, $language, $this->permalinkService, $navigations, $allLanguages);
    }
}
