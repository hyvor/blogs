<?php

namespace App\Api\Console\Input\Navigation;

use App\Entity\Enum\NavigationType;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateNavigationInput
{
    #[Assert\NotBlank]
    public string $url;

    #[Assert\NotNull]
    public NavigationType $type;
}
