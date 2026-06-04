<?php

namespace App\Api\Data\Object;

use App\Entity\Language;
use App\Entity\Navigation;

class NavObject
{
    public string $url;
    public ?string $name;
    public bool $open_in_new_tab;

    public function __construct(Navigation $nav, Language $language)
    {
        $this->url = $nav->getUrl();
        $this->open_in_new_tab = false;
        $name = null;
        foreach ($nav->getVariants() as $variant) {
            if ($variant->getLanguageId() === $language->getId()) {
                $name = $variant->getName();
                break;
            }
        }
        if ($name === null) {
            $first = $nav->getVariants()->first();
            $name = $first ? $first->getName() : null;
        }
        $this->name = $name;
    }
}
