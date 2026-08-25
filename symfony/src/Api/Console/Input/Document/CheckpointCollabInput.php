<?php

namespace App\Api\Console\Input\Document;

use App\Service\Post\Content\Validation\ProsemirrorJson;
use Symfony\Component\Validator\Constraints as Assert;

class CheckpointCollabInput
{
    #[Assert\NotNull]
    public int $post_variant_id;

    #[Assert\GreaterThanOrEqual(0)]
    public int $version;

    #[Assert\NotNull]
    #[ProsemirrorJson]
    public string $content;
}
