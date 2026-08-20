<?php

namespace App\Api\Console\Input\Post;

use App\Service\Post\Content\Validation\ProsemirrorJson;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

    // false = not provided (leave untouched); null = clear; string = set to this JSON
    #[ProsemirrorJson]
    public null|string|false $content = false;

    // false = not provided (leave untouched); null = clear; string = set to this JSON
    #[ProsemirrorJson]
    public null|string|false $content_unsaved = false;

    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 350)]
    public ?string $description = null;

    #[Assert\Length(max: 255)]
    public null|string|false $seo_primary_keyword = false;

    /** @var string[]|null */
    #[Assert\All([new Assert\Length(max: 255)])]
    public ?array $seo_secondary_keywords = null;

    public bool $redirect_on_slug_change = false;

    // false = not provided (leave untouched); null = clear; int = set to this timestamp
    public null|int|false $content_updated_at = false;
}
