<?php

namespace App\Api\Console\Input\Blog\User;

use Symfony\Component\Validator\Constraints as Assert;

class CheckUserSlugAvailableInput
{
    #[Assert\NotBlank]
    public string $slug;
}
