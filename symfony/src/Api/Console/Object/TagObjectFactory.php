<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Tag;

class TagObjectFactory
{
    public function __construct(private TagVariantObjectFactory $variantFactory) {}

    public function create(Tag $tag, Blog $blog): TagObject
    {
        $variants = $tag->getVariants()->toArray();
        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());

        $variantObjects = array_map(
            fn($variant) => $this->variantFactory->create($variant, $tag, $blog),
            $variants,
        );

        return new TagObject($tag, $variantObjects);
    }
}
