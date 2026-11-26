<?php

namespace App\Api\Console\Input\Post;

use App\Service\Post\Content\PostContentFormat;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;

    /** Replaces the whole draft content, parsed according to `format` */
    public ?string $content = null;

    public PostContentFormat $format = PostContentFormat::MARKDOWN;

    /**
     * `version` from GET /post/{id}/variant. If set and the document has changed since,
     * the request fails with 409 and nothing is updated. If null, `content` overwrites the document.
     */
    #[Assert\PositiveOrZero]
    public ?int $base_version = null;

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

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

    public null|int|false $content_updated_at = false;

    public null|int|false $published_at = false;

    #[Assert\Range(min: 0, max: 100)]
    public ?int $seo_score = null;
}
