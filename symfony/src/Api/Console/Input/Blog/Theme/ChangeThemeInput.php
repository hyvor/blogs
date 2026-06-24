<?php

namespace App\Api\Console\Input\Blog\Theme;

use Symfony\Component\Validator\Constraints as Assert;

class ChangeThemeInput
{
    #[Assert\NotBlank]
    public string $name;
}
