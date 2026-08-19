<?php

namespace App\Api\Console\Input\Post;

use App\Entity\Enum\PostVariantContentType;
use Symfony\Component\Validator\Constraints as Assert;

class SyncCollabStepsInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotNull]
    public PostVariantContentType $type;

    // the client's current collab version - every step after this one is returned
    #[Assert\GreaterThanOrEqual(0)]
    public int $version;
}
