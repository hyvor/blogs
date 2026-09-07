<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;

    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 255)]
    public ?string $bio = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;
}
