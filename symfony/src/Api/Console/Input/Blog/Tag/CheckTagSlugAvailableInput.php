<?php

namespace App\Api\Console\Input\Blog\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class CheckTagSlugAvailableInput
{
    #[Assert\NotBlank]
    public string $slug;
}
