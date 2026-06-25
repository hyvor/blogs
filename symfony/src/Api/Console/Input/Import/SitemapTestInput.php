<?php

namespace App\Api\Console\Input\Import;

use Symfony\Component\Validator\Constraints as Assert;

class SitemapTestInput
{
    #[Assert\NotBlank]
    public string $url = '';

    /** @var array<string, string|null> */
    public array $css = [];

    public ?string $slug_exclude = null;
}
