<?php

namespace App\Api\Console\Input\Post;

use Hyvor\Internal\Util\Dto\HasOptionalProperties;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostInput
{

    use HasOptionalProperties;

    public bool $is_featured;

    #[Assert\Length(max: 255)]
    public ?string $canonical_url;

    #[Assert\Length(max: 255)]
    public ?string $featured_image_url;

    public ?string $code_head;

    public ?string $code_foot;

    public ?int $published_at;
}
