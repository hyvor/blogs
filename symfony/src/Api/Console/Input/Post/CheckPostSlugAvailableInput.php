<?php

namespace App\Api\Console\Input\Post;

use Symfony\Component\Validator\Constraints as Assert;

class CheckPostSlugAvailableInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotBlank]
    public string $slug;
}
