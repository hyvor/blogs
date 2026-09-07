<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
