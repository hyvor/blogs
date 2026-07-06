<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTagVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
