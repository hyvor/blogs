<?php

namespace App\Api\Console\Input\Post;

use App\Entity\Enum\PostVariantContentType;
use Symfony\Component\Validator\Constraints as Assert;

class SubmitCollabStepsInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotNull]
    public PostVariantContentType $type;

    #[Assert\GreaterThanOrEqual(0)]
    public int $version;

    /** @var array<int, array<string, mixed>> */
    #[Assert\NotNull]
    public array $steps;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $client_id;
}
