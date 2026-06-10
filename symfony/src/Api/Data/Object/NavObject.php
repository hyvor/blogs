<?php

namespace App\Api\Data\Object;

use App\Entity\Language;
use App\Entity\Navigation;

class NavObject
{
    public string $name;
    public string $url;

    public function __construct(Navigation $nav, Language $language)
    {
        $this->url = $nav->getUrl();

        $variants = $nav->getVariants();
        $selectedVariant = $variants[0] ?? null;
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->getId() === $language->getId()) {
                $selectedVariant = $variant;
            }
        }

        $this->name = $selectedVariant?->getName() ?? '';
    }
}
