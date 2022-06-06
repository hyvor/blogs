<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Models\UserVariant;

class UserVariantObject
{
    public int $user_id;
    public int $language_id;
    public ?string $name;
    public ?string $bio;
    public ?string $location;


    public function __construct(UserVariant $variant)
    {
        $this->user_id = $variant->user_id;
        $this->language_id = $variant->language_id;

        $this->name = $variant->name;
        $this->bio = $variant->bio;
        $this->location = $variant->location;
    }
}
