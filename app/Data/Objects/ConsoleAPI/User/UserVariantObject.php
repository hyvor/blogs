<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Models\UserVariant;

class UserVariantObject
{
    public int $language_id;
    public ?string $name;
    public ?string $bio;
    public ?string $location;


    public function __construct(UserVariant $userVariant)
    {
        $language = $userVariant->language;

        $this->language_id = $language->id;

        $this->name = $userVariant->name;
        $this->bio = $userVariant->bio;
        $this->location = $userVariant->location;
    }
}
