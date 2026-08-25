<?php

namespace App\Api\Console\Input\Document;

use Symfony\Component\Validator\Constraints as Assert;

class GetDocumentForPostInput
{

    #[Assert\NotBlank]
    public int $post_id;

    public ?string $variant_language_code = null;
}
