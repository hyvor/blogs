<?php

namespace App\Api\Console\Input\Blog\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateGuestUserInput
{
    #[Assert\NotBlank]
    public string $name;
}
