<?php

namespace App\Api\Console\Input\Navigation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNavigationVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;

    public ?string $name = null;
}
