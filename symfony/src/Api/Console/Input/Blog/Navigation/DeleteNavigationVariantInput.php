<?php

namespace App\Api\Console\Input\Blog\Navigation;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteNavigationVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
