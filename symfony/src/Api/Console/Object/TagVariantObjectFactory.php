<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Service\Route\PermalinkService;

class TagVariantObjectFactory
{
    public function __construct(private PermalinkService $permalink) {}

    public function create(TagVariant $variant, Tag $tag, Blog $blog): TagVariantObject
    {
        $url = $this->permalink->getTagPermalink($tag, $blog, $variant->getLanguage());

        return new TagVariantObject($variant, $url);
    }
}
