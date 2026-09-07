<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateGuestUserInput
{
    #[Assert\NotBlank]
    public string $name;
}
