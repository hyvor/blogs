<?php

namespace App\Api\Console\Object;

use App\Entity\Navigation;
use App\Entity\NavigationVariant;

class NavigationObject
{
    public int $id;
    public int $created_at;
    public string $url;
    public string $type;
    public int $sort;
    /** @var NavigationVariantObject[] */
    public array $variants;

    public function __construct(Navigation $navigation)
    {
        $this->id = $navigation->getId();
        $this->created_at = $navigation->getCreatedAt()->getTimestamp();
        $this->url = $navigation->getUrl();
        $this->type = $navigation->getType();
        $this->sort = $navigation->getSort();

        /** @var NavigationVariant[] $variantEntities */
        $variantEntities = $navigation->getVariants()->toArray();
        $this->variants = array_values(array_map(
            fn(NavigationVariant $v) => new NavigationVariantObject($v),
            $variantEntities,
        ));
        usort($this->variants, fn(NavigationVariantObject $a, NavigationVariantObject $b) => $a->language_id <=> $b->language_id);
    }
}
