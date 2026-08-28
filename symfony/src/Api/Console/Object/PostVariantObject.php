<?php

namespace App\Api\Console\Object;

use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Route\PermalinkService;

class PostVariantObject
{
    public int $id;
    public int $language_id;
    public ?string $slug;
    public string $status;
    public string $url;
    public ?string $content;
    public ?string $title;
    public ?string $description;
    public ?int $content_updated_at;
    public ?string $seo_primary_keyword;
    /** @var string[] */
    public array $seo_secondary_keywords;
    /** @var array<string, mixed> */
    public array $link_analysis;
    public ?int $seo_score;

    public function __construct(
        PostVariant $variant,
        Post $post,
        PermalinkService $permalinkService,
    ) {
        $this->id = $variant->getId();
        $this->language_id = $variant->getLanguage()->getId();
        $this->slug = $variant->getSlug();
        $this->status = $variant->getStatus()->value;
        $this->url = $permalinkService->getPostVariantPermalink($variant);
        $this->content = $variant->getContent();
        $this->title = $variant->getTitle();
        $this->description = $variant->getDescription();
        $this->content_updated_at = $variant->getContentUpdatedAt()?->getTimestamp();
        $this->seo_primary_keyword = $variant->getSeoPrimaryKeyword();
        $this->seo_secondary_keywords = $variant->getSeoSecondaryKeywords() ?? [];
        $this->link_analysis = $variant->getLinkAnalysis() ?? [];
        $this->seo_score = $variant->getSeoScore();
    }
}
