<?php

namespace App\Api\Console\Input\Blog\Theme;

use Symfony\Component\Validator\Constraints as Assert;

class CheckThemeFileNameAvailableInput
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Choice(choices: ['templates', 'assets', 'styles', 'lang'])]
    public ?string $folder = null;
}
