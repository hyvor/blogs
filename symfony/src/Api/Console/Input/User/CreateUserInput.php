<?php

namespace App\Api\Console\Input\User;

use App\Entity\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $hyvor_user_id;

    #[Assert\NotNull]
    public UserRole $role;
}
