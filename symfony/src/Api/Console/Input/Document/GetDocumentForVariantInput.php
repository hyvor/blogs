<?php

namespace App\Api\Console\Input\Document;

use Symfony\Component\Validator\Constraints as Assert;

class GetDocumentForVariantInput
{

    #[Assert\NotBlank]
    public int $post_variant_id;

}
