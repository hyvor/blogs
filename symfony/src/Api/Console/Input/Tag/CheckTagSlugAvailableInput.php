<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class CheckTagSlugAvailableInput
{
    #[Assert\NotBlank]
    public string $slug;
}
