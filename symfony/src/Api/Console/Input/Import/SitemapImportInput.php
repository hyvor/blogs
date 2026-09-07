<?php

namespace App\Api\Console\Input\Import;

use Symfony\Component\Validator\Constraints as Assert;

class SitemapImportInput
{
    #[Assert\NotBlank]
    public string $sitemap_url = '';

    public bool $import_images = false;

    /** @var array<string, string|null> */
    public array $css = [];

    public ?string $slug_exclude = null;
}
