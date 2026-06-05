<?php

namespace App\Entity\Meta;

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
    public string $seo_external_links_follow = 'follow';
    public bool $seo_rich_schema = true;

    public ?string $comments_code = null;
    public ?string $newsletter_code = null;

    public string $color_modes = 'both';
    public string $color_mode_default = 'os';

    public bool $syntax_on = true;
    public bool $syntax_line_numbers = true;
    public ?string $syntax_theme = null;

    public bool $heading_anchors = true;
    public bool $flashload = true;

    public bool $link_analysis_enabled = true;
    public string $link_analysis_email_report = 'broken';

    public int $cache_version_styles = 1;
}
