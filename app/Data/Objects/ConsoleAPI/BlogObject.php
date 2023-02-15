<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ColorModeDefaultEnum;
use App\Data\Enums\ColorModesEnum;
use App\Data\Enums\CommentsTypeEnum;
use App\Data\Enums\SeoExternalLinksFollowEnum;
use App\Models\Blog;

class BlogObject
{
    public int $id;

    public int $created_at;
    public bool $is_blocked;

    public string $subdomain;

    public BlogTypeEnum $type;

    public BlogHostingAtEnum $hosting_at;

    public ?string $hosting_domain;

    public ?string $hosting_url;

    public ?string $logo_url;

    public ?string $cover_url;

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

    public SeoExternalLinksFollowEnum $seo_external_links_follow;

    public CommentsTypeEnum $comments_type;

    public ?int $comments_ht_website_id;

    public ?string $comments_ht_api_key;

    public ?string $comments_code;

    public ?string $newsletter_code;

    public ColorModesEnum $color_modes;

    public ColorModeDefaultEnum $color_mode_default;

    public bool $syntax_on;

    public bool $syntax_line_numbers;

    public ?string $syntax_theme;

    public bool $flashload;

    /**
     * @var BlogVariantObject[]
     */
    public array $variants;

    public function __construct(Blog $blog)
    {
        $this->id = $blog->id;
        $this->created_at = $blog->created_at->timestamp;
        $this->is_blocked = $blog->is_blocked;

        $this->subdomain = $blog->subdomain;
        $this->hosting_at = $blog->hosting_at;
        $this->hosting_domain = $blog->hosting_domain;
        $this->hosting_url = $blog->hosting_url;

        $meta = $blog->getAllMeta();

        $this->logo_url = $meta->logo_url;
        $this->cover_url = $meta->cover_url;

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

        $this->comments_type = CommentsTypeEnum::from($meta->comments_type);
        $this->comments_ht_website_id = $meta->comments_ht_website_id;
        $this->comments_ht_api_key = $meta->comments_ht_api_key;
        $this->comments_code = $meta->comments_code;

        $this->newsletter_code = $meta->newsletter_code;

        $this->seo_indexing = (bool) $meta->seo_indexing;
        $this->seo_robots_txt = $meta->seo_robots_txt;
        $this->seo_external_links_follow = SeoExternalLinksFollowEnum::from($meta->seo_external_links_follow);

        $this->color_modes = ColorModesEnum::from($meta->color_modes);
        $this->color_mode_default = ColorModeDefaultEnum::from($meta->color_mode_default);

        $this->syntax_on = (bool) $meta->syntax_on;
        $this->syntax_line_numbers = (bool) $meta->syntax_line_numbers;
        $this->syntax_theme = $meta->syntax_theme;

        $this->flashload = (bool) $meta->flashload;

        $this->variants = $blog->variants->map(function ($variant) use ($blog) {
            return new BlogVariantObject($variant, $blog);
        })->sortBy('language_id')->toArray();
    }
}
