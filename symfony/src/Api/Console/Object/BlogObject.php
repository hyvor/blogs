<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Enum\Blog\ColorMode;
use App\Entity\Enum\Blog\ColorModeDefault;
use App\Entity\Enum\Blog\LinkAnalysisEmailReport;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\BlogType;

class BlogObject
{
    public int $id;

    public int $created_at;
    public bool $is_blocked;
    public ?int $theme_version_id;

    public string $subdomain;

    public BlogType $type;

    public BlogHostingAt $hosting_at;

    public ?string $hosting_domain;

    public ?string $hosting_url;

    public bool $hosting_redirect_subdomain;

    public string $url;

    public ?string $logo_url;
    public ?string $cover_url;
    public ?string $icon_url;

    // meta
    public bool $embeddable;

    public ?string $embedding_domains;

    public ?string $social_facebook;
    public ?string $social_twitter;
    public ?string $social_linkedin;
    public ?string $social_youtube;
    public ?string $social_tiktok;
    public ?string $social_instagram;
    public ?string $social_github;

    public ?string $code_head;
    public ?string $code_foot;

    public bool $seo_indexing;
    public ?string $seo_robots_txt;
    public bool $seo_rich_schema;
    public SeoExternalLinksFollow $seo_external_links_follow;

    public ?string $comments_code;
    public ?string $newsletter_code;

    public ColorMode $color_modes;
    public ColorModeDefault $color_mode_default;

    public bool $syntax_on;
    public bool $syntax_line_numbers;
    public ?string $syntax_theme;

    public bool $heading_anchors;

    public bool $flashload;

    public bool $link_analysis_enabled;
    public LinkAnalysisEmailReport $link_analysis_email_report;

    /**
     * @var BlogVariantObject[]
     */
    public array $variants;

    public function __construct(Blog $blog, string $url)
    {
        $this->id = $blog->getId();
        $this->created_at = $blog->getCreatedAt()?->getTimestamp() ?? 0;
        $this->is_blocked = $blog->isBlocked();
        $this->theme_version_id = $blog->getThemeVersion()?->getId();

        $this->subdomain = $blog->getSubdomain();
        $this->type = $blog->getType();
        $this->hosting_at = $blog->getHostingAt();
        $this->hosting_domain = $blog->getCustomDomain()?->getDomain();
        $this->hosting_url = $blog->getHostingUrl();
        $this->hosting_redirect_subdomain = $blog->getHostingRedirectSubdomain();

        $this->url = $url;

        $meta = $blog->getMeta();

        $this->logo_url = $meta->logo_url;
        $this->cover_url = $meta->cover_url;
        $this->icon_url = $meta->icon_url;

        $this->embeddable = $meta->embeddable;
        $this->embedding_domains = $meta->embedding_domains;

        $this->social_facebook = $meta->social_facebook;
        $this->social_twitter = $meta->social_twitter;
        $this->social_linkedin = $meta->social_linkedin;
        $this->social_youtube = $meta->social_youtube;
        $this->social_tiktok = $meta->social_tiktok;
        $this->social_instagram = $meta->social_instagram;
        $this->social_github = $meta->social_github;

        $this->code_head = $meta->code_head;
        $this->code_foot = $meta->code_foot;

        $this->seo_indexing = $meta->seo_indexing;
        $this->seo_robots_txt = $meta->seo_robots_txt;
        $this->seo_rich_schema = $meta->seo_rich_schema;
        $this->seo_external_links_follow = $meta->seo_external_links_follow;

        $this->comments_code = $meta->comments_code;
        $this->newsletter_code = $meta->newsletter_code;

        $this->color_modes = $meta->color_modes;
        $this->color_mode_default = $meta->color_mode_default;

        $this->syntax_on = $meta->syntax_on;
        $this->syntax_line_numbers = $meta->syntax_line_numbers;
        $this->syntax_theme = $meta->syntax_theme;

        $this->heading_anchors = $meta->heading_anchors;

        $this->flashload = $meta->flashload;

        $this->link_analysis_enabled = $meta->link_analysis_enabled;
        $this->link_analysis_email_report = $meta->link_analysis_email_report;

        $variants = $blog->getVariants()->toArray();
        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());

        $this->variants = array_map(fn($variant) => new BlogVariantObject($variant), $variants);
    }
}
