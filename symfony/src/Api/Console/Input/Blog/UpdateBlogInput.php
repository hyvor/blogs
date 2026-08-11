<?php

namespace App\Api\Console\Input\Blog;

use App\Entity\Enum\Blog\ColorMode;
use App\Entity\Enum\Blog\ColorModeDefault;
use App\Entity\Enum\Blog\LinkAnalysisEmailReport;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Service\Ai\AiProvider;
use App\Service\Blog\BlogService;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateBlogInput
{
    #[Assert\Regex(BlogService::SUBDOMAIN_REGEX)]
    public ?string $subdomain = null;

    public ?bool $hosting_redirect_subdomain = null;

    // meta
    public ?bool $embeddable = null;

    public ?string $embedding_domains = null;

    #[Assert\Url]
    public ?string $logo_url = null;

    #[Assert\Url]
    public ?string $icon_url = null;

    #[Assert\Url]
    public ?string $cover_url = null;

    #[Assert\Url]
    public ?string $social_facebook = null;

    #[Assert\Url]
    public ?string $social_twitter = null;

    #[Assert\Url]
    public ?string $social_linkedin = null;

    #[Assert\Url]
    public ?string $social_youtube = null;

    #[Assert\Url]
    public ?string $social_tiktok = null;

    #[Assert\Url]
    public ?string $social_instagram = null;

    #[Assert\Url]
    public ?string $social_github = null;

    public ?string $code_head = null;

    public ?string $code_foot = null;

    public ?bool $seo_indexing = null;

    public ?string $seo_robots_txt = null;

    public ?SeoExternalLinksFollow $seo_external_links_follow = null;

    public ?bool $seo_rich_schema = null;

    public ?string $comments_code = null;

    public ?string $newsletter_code = null;

    public ?ColorMode $color_modes = null;

    public ?ColorModeDefault $color_mode_default = null;

    public ?bool $syntax_on = null;

    public ?bool $syntax_line_numbers = null;

    public ?string $syntax_theme = null;

    public ?bool $heading_anchors = null;

    public ?bool $link_analysis_enabled = null;

    public ?LinkAnalysisEmailReport $link_analysis_email_report = null;

    public ?AiProvider $ai_provider = null;

    public ?bool $ai_translation_enabled = null;

    public ?bool $ai_generation_enabled = null;
}
