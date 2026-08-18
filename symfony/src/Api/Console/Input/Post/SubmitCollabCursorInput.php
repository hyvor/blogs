<?php

namespace App\Api\Console\Input\Post;

use App\Entity\Enum\PostVariantContentType;
use Symfony\Component\Validator\Constraints as Assert;

class SubmitCollabCursorInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotNull]
    public PostVariantContentType $type;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $client_id;

    // null means the cursor left the editor (blur) - both must be null/non-null together,
    // enforced by SubmitCollabCursorInput's caller-side symmetry (see plugin-cursors.ts'
    // `{ from, to } | null` union)
    #[Assert\PositiveOrZero]
    public ?int $from = null;

    #[Assert\PositiveOrZero]
    public ?int $to = null;
}
