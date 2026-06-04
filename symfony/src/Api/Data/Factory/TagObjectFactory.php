<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\TagObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Service\Route\PermalinkService;

class TagObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    /**
     * @param array<array{language: Language, name: ?string, description: ?string}> $variantData
     */
    public function create(Tag $tag, Blog $blog, Language $language, array $variantData = []): TagObject
    {
        return new TagObject($tag, $blog, $language, $this->permalinkService, $variantData);
    }
}
