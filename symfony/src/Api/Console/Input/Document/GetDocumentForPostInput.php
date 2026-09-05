<?php

namespace App\Api\Console\Input\Document;

use Symfony\Component\Validator\Constraints as Assert;

class GetDocumentForPostInput
{

    #[Assert\When(
        'this.post_id === null',
        constraints: [
            new Assert\NotNull(message: 'post_id is required when post_variant_id is not provided'),
        ]
    )]
    public ?int $post_variant_id = null;

    #[Assert\When(
        'this.post_variant_id === null',
        constraints: [
            new Assert\NotNull(message: 'post_id is required when post_variant_id is not provided'),
        ]
    )]
    public ?int $post_id = null;
    public ?string $variant_language_code = null;
}
