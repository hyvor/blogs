<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTagVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;

    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 255)]
    public ?string $description = null;
}
