<?php

namespace App\Api\Console\Input\Blog\Post;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

    #[Assert\Choice(choices: ['draft', 'published', 'scheduled'])]
    public ?string $status = null;

    public ?string $content = null;

    public ?string $content_unsaved = null;

    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 350)]
    public ?string $description = null;

    #[Assert\Length(max: 255)]
    public ?string $seo_primary_keyword = null;

    /** @var string[]|null */
    public ?array $seo_secondary_keywords = null;

    public bool $redirect_on_slug_change = false;
}
