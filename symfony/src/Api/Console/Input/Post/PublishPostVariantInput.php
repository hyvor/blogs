<?php

namespace App\Api\Console\Input\Post;

use Symfony\Component\Validator\Constraints as Assert;

class PublishPostVariantInput
{
    #[Assert\NotNull]
    public int $post_variant_id;

    // null = now, timestamp = schedule at timestamp
    public ?int $publish_at = null;
}
