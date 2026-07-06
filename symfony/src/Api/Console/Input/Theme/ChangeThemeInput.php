<?php

namespace App\Api\Console\Input\Theme;

use Symfony\Component\Validator\Constraints as Assert;

class ChangeThemeInput
{
    #[Assert\NotBlank]
    public string $name;

    public ?string $version = null;
}
