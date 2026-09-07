<?php

namespace App\Entity\Meta;

use App\Entity\Enum\Blog\ColorMode;
use App\Entity\Enum\Blog\ColorModeDefault;
use App\Entity\Enum\Blog\LinkAnalysisEmailReport;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Service\Ai\AiModel;

class BlogMeta
{
    public bool $embeddable = false;
    public ?string $embedding_domains = null;

    public ?string $logo_url = null;
    public ?string $cover_url = null;
    public ?string $icon_url = null;

    public ?string $social_facebook = null;
    public ?string $social_twitter = null;
    public ?string $social_linkedin = null;
    public ?string $social_youtube = null;
    public ?string $social_tiktok = null;
    public ?string $social_instagram = null;
    public ?string $social_github = null;

    public ?string $code_head = null;
    public ?string $code_foot = null;

    public bool $seo_indexing = true;
    public string $seo_robots_txt = "User-agent: *\nSitemap: {{ _blog.base_url }}/sitemap.xml\nDisallow: /p/";
    public SeoExternalLinksFollow $seo_external_links_follow = SeoExternalLinksFollow::FOLLOW;
    public bool $seo_rich_schema = true;

    public ?string $comments_code = null;
    public ?string $newsletter_code = null;

    public ColorMode $color_modes = ColorMode::BOTH;
    public ColorModeDefault $color_mode_default = ColorModeDefault::OS;

    public bool $syntax_on = true;
    public bool $syntax_line_numbers = true;
    public ?string $syntax_theme = null;

    public bool $heading_anchors = true;
    public bool $flashload = true;

    public bool $link_analysis_enabled = true;
    public LinkAnalysisEmailReport $link_analysis_email_report = LinkAnalysisEmailReport::BROKEN;

    public AiModel $ai_model = AiModel::GPT_5_6_LUNA;
    public bool $ai_translation_enabled = true;
    public bool $ai_agent = true;

    public int $cache_version_styles = 1;
}
