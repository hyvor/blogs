<?php

namespace App\Api\Console\Object;

use App\Entity\UserVariant;

class UserVariantObject
{
    public int $user_id;
    public int $language_id;
    public string $url;
    public ?string $name;
    public ?string $bio;
    public ?string $location;

    public function __construct(UserVariant $variant, string $url)
    {
        $this->user_id = $variant->getUser()->getId();
        $this->language_id = $variant->getLanguage()->getId();
        $this->url = $url;
        $this->name = $variant->getName();
        $this->bio = $variant->getBio();
        $this->location = $variant->getLocation();
    }
}
