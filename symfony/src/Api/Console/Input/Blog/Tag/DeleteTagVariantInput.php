<?php

namespace App\Api\Console\Input\Blog\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteTagVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
