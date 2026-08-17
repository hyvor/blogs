<?php

namespace App\Api\Console\Input\Post;

use App\Entity\Enum\PostVariantContentType;
use App\Service\Post\Content\Validation\ProsemirrorJson;
use Symfony\Component\Validator\Constraints as Assert;

class CheckpointCollabInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotNull]
    public PostVariantContentType $type;

    #[Assert\GreaterThanOrEqual(0)]
    public int $version;

    #[Assert\NotNull]
    #[ProsemirrorJson]
    public string $content;
}
