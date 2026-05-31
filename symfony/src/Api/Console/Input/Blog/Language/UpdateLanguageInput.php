<?php

namespace App\Api\Console\Input\Blog\Language;

use App\Entity\Enum\LanguageDirection;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateLanguageInput
{
    #[Assert\Length(max: 12)]
    public ?string $code = null;

    #[Assert\Length(max: 255)]
    public ?string $name = null;

    public ?LanguageDirection $direction = null;
}
