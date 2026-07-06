<?php

namespace App\Api\Console\Input\Blog\Post;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostInput
{
    public ?bool $is_featured = null;

    #[Assert\Length(max: 255)]
    public ?string $canonical_url = null;

    #[Assert\Length(max: 255)]
    public ?string $featured_image_url = null;

    public ?string $code_head = null;

    public ?string $code_foot = null;

    public ?int $published_at = null;
}
