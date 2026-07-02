<?php

namespace App\Api\Console\Input\Blog\LinkAnalysis;

use Symfony\Component\Validator\Constraints as Assert;

class CheckPostVariantLinksInput
{
    public int $post_variant_id;

    /** @var string[] */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    public array $urls = [];
}
