<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\TagObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class TagObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    public function create(Tag $tag, Blog $blog, Language $language): TagObject
    {
        return new TagObject($tag, $blog, $language, $this->permalinkService);
    }

}
