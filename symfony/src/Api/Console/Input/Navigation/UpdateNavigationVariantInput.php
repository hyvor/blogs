<?php

namespace App\Api\Console\Input\Navigation;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateNavigationVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;

    #[Assert\NotBlank]
    public string $name;
}
