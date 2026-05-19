<?php

namespace App\Api\Console\Input\Blog\Language;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateLanguageInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 12)]
    public string $code;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\Choice(['ltr', 'rtl'])]
    public string $direction = 'ltr';
}
