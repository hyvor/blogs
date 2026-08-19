<?php

namespace App\Api\Console\Input\Post;

use Symfony\Component\Validator\Constraints as Assert;

class SyncCollabStepsInput
{
    #[Assert\NotNull]
    public int $post_variant_id;

    // the client's current collab version - every step after this one is returned
    #[Assert\GreaterThanOrEqual(0)]
    public int $version;
}
