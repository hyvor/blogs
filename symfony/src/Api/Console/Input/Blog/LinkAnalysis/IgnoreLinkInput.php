<?php

namespace App\Api\Console\Input\Blog\LinkAnalysis;

use Symfony\Component\Validator\Constraints as Assert;

class IgnoreLinkInput
{
    public int $post_variant_id;

    #[Assert\NotBlank]
    public string $url;

    public bool $status;
}
