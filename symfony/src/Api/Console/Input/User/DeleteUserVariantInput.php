<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteUserVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
