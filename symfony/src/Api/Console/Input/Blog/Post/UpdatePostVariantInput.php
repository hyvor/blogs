<?php

namespace App\Api\Console\Input\Blog\Post;

use App\Entity\Enum\PostVariantStatus;
use App\Service\Post\Content\Validation\ProsemirrorJson;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

    public ?PostVariantStatus $status = null;

    #[ProsemirrorJson]
    public ?string $content = null;

    #[ProsemirrorJson]
    public ?string $content_unsaved = null;

    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 350)]
    public ?string $description = null;

    #[Assert\Length(max: 255)]
    public null|string|false $seo_primary_keyword = false;

    #[Assert\All([new Assert\Length(max: 255)])]
    /** @var string[]|null */
    public ?array $seo_secondary_keywords = null;

    public bool $redirect_on_slug_change = false;
}
