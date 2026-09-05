<?php

namespace App\Api\Console\Object\Ai;

use App\Service\Post\Content\Validation\ProsemirrorJson;
use Symfony\Component\Validator\Constraints as Assert;

class ApplyDocumentChangeInput
{
    #[Assert\NotBlank]
    public int $event_id;

    #[Assert\NotBlank]
    #[ProsemirrorJson]
    public string $content;

    #[Assert\NotBlank]
    public int $agent_version;

    // apply the change even if the post is updated after agent_version
    public bool $force = false;
}
