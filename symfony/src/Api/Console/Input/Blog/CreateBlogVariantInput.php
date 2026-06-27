<?php

namespace App\Api\Console\Input\Blog;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBlogVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;
}
