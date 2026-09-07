<?php

namespace App\Api\Console\Input\Blog;

use App\Service\Limit;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateBlogVariantInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $language_id;

    #[Assert\Length(max: Limit::MAX_BLOG_NAME_LENGTH)]
    public ?string $name = null;

    #[Assert\Length(max: Limit::MAX_BLOG_DESCRIPTION_LENGTH)]
    public ?string $description = null;
}
