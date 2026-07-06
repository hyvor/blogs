<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTagInput
{
    #[Assert\NotBlank]
    public string $name;

    public bool $is_private = false;
}
