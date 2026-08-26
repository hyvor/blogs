<?php

namespace App\Api\Console\Input\Document;

use Symfony\Component\Validator\Constraints as Assert;

class SubmitCollabCursorInput
{
    #[Assert\NotNull]
    public int $post_variant_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $client_id;

    // null means the cursor left the editor (blur) - both must be null/non-null together
    #[Assert\PositiveOrZero]
    public ?int $from = null;

    #[Assert\PositiveOrZero]
    public ?int $to = null;
}
