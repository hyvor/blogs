<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Models\UsersVariant;

class UserVariantObject
{
    public int $id;
    public int $user_id;
    public int $language_id;
    public ?string $name;
    public ?string $bio;
    public ?string $location;


    public function __construct(UsersVariant $userVariant)
    {
        $language = $userVariant->language;
        $this->id = $userVariant->id;
        $this->user_id = $userVariant->user_id;
        $this->language_id = $language->id;

        $this->name = $userVariant->name;
        $this->bio = $userVariant->bio;
        $this->location = $userVariant->location;

    }
} 
