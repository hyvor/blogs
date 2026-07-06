<?php

namespace App\Api\Console\Input\Theme;

use App\Entity\Enum\ThemeFileFolder;
use Symfony\Component\Validator\Constraints as Assert;

class CreateThemeFileInput
{
    public ?ThemeFileFolder $folder = null;

    #[Assert\NotBlank]
    public string $name;

    public string $content = '';
}
