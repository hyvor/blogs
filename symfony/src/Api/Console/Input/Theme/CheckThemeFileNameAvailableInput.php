<?php

namespace App\Api\Console\Input\Theme;

use App\Entity\Enum\ThemeFileFolder;
use Symfony\Component\Validator\Constraints as Assert;

class CheckThemeFileNameAvailableInput
{
    #[Assert\NotBlank]
    public string $name;

    public ?ThemeFileFolder $folder = null;
}
