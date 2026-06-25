<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;

class PostVariantObject
{
    public int $id;
    public int $language_id;
    public int $post_id;
    public ?string $slug;
    public string $status;
    public string $url;
    public ?string $content;
    public ?string $content_unsaved;
    public ?string $title;
    public ?string $description;
    public ?string $seo_primary_keyword;
    /** @var string[] */
    public array $seo_secondary_keywords;
    /** @var array<string, mixed> */
    public array $link_analysis;
    public ?string $content_html = null;

    public function __construct(
        PostVariant $variant,
        Post $post,
        Blog $blog,
        PermalinkService $permalinkService,
        ?PostContentService $postContentService = null,
    ) {
        $this->id = $variant->getId();
        $this->language_id = $variant->getLanguage()->getId();
        $this->post_id = $post->getId();
        $this->slug = $variant->getSlug();
        $this->status = $variant->getStatus()->value;
        $this->url = $permalinkService->getPostPermalink($post, $blog, $variant->getLanguage());
        $this->content = $variant->getContent();
        $this->content_unsaved = $variant->getContentUnsaved();
        $this->title = $variant->getTitle();
        $this->description = $variant->getDescription();
        $this->seo_primary_keyword = $variant->getSeoPrimaryKeyword();
        $this->seo_secondary_keywords = $variant->getSeoSecondaryKeywords() ?? [];
        $this->link_analysis = $variant->getLinkAnalysis() ?? [];

        if ($postContentService !== null && $this->content !== null) {
            $this->content_html = $postContentService->getHtml($this->content, $blog);
        }
    }
}
