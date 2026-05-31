<?php

namespace App\Api\Console\Input\Blog\Navigation;

use App\Entity\Enum\NavigationType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateNavigationInput
{
    #[Assert\NotBlank]
    public string $url;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotNull]
    public NavigationType $type;
}
