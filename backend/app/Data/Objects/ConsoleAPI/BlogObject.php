<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ColorModeDefaultEnum;
use App\Data\Enums\ColorModesEnum;
use App\Data\Enums\LinkAnalysisEmailReportEnum;
use App\Data\Enums\SeoExternalLinksFollowEnum;
use App\Models\Blog;

class BlogObject
{
    public int $id;

    public int $trial_ends_at;
    public int $created_at;
    public bool $is_blocked;
    public ?int $theme_version_id;

    public string $subdomain;

    public BlogTypeEnum $type;

    public BlogHostingAtEnum $hosting_at;

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

    public SeoExternalLinksFollowEnum $seo_external_links_follow;

    public ?string $comments_code;

    public ?string $newsletter_code;

    public ColorModesEnum $color_modes;

    public ColorModeDefaultEnum $color_mode_default;

    public bool $syntax_on;

    public bool $syntax_line_numbers;

    public ?string $syntax_theme;

    public bool $heading_anchors;

    public bool $flashload;

    public bool $link_analysis_enabled;
    public LinkAnalysisEmailReportEnum $link_analysis_email_report;

    public ?bool $hb_branding;

    /**
     * @var BlogVariantObject[]
     */
    public array $variants;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->created_at = $blog->created_at->getTimestamp();
        $this->trial_ends_at = $blog->trial_ends_at->getTimestamp();
        $this->is_blocked = $blog->is_blocked;
        $this->theme_version_id = $blog->theme_version_id;

        $this->subdomain = $blog->subdomain;
        $this->type = $blog->type;
        $this->hosting_at = $blog->hosting_at;
        $this->hosting_domain = $blog->hosting_domain;
        $this->hosting_url = $blog->hosting_url;
        $this->hosting_redirect_subdomain = $blog->hosting_redirect_subdomain;

        $this->url = $blog->url();

        $meta = $blog->getAllMeta();

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

        $this->comments_code = $meta->comments_code;
        $this->newsletter_code = $meta->newsletter_code;

        $this->seo_indexing = (bool) $meta->seo_indexing;
        $this->seo_robots_txt = $meta->seo_robots_txt;
        $this->seo_external_links_follow = SeoExternalLinksFollowEnum::from($meta->seo_external_links_follow);
        $this->seo_rich_schema = (bool) $meta->seo_rich_schema;
        $this->color_modes = ColorModesEnum::from($meta->color_modes);
        $this->color_mode_default = ColorModeDefaultEnum::from($meta->color_mode_default);

        $this->syntax_on = (bool) $meta->syntax_on;
        $this->syntax_line_numbers = (bool) $meta->syntax_line_numbers;
        $this->syntax_theme = $meta->syntax_theme;

        $this->heading_anchors = (bool) $meta->heading_anchors;

        $this->flashload = (bool) $meta->flashload;

        $this->link_analysis_enabled = (bool) $meta->link_analysis_enabled;
        $this->link_analysis_email_report = LinkAnalysisEmailReportEnum::tryFrom($meta->link_analysis_email_report);

        $this->hb_branding = $meta->hb_branding;

        $this->variants = $blog->variants->map(function ($variant) use ($blog) {
            return new BlogVariantObject($variant);
        })->sortBy('language_id')->toArray();
    }
}
