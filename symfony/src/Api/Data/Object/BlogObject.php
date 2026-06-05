<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Enum\NavigationType;
use App\Entity\Language;
use App\Service\Route\PermalinkService;

class BlogObject
{
    public string $type;
    public string $subdomain;
    public ?string $name;
    public ?string $description;
    public string $url;
    public string $base_url;
    public ?string $logo_url;
    public ?string $icon_url;
    public ?string $cover_url;
    public ?string $code_head;
    public ?string $code_foot;
    public bool $seo_indexing;
    public bool $seo_rich_schema;
    public bool $flashload;
    public string $color_modes;
    public string $color_mode_default;
    public int $cache_version_styles;
    public int $posts_count;
    public SocialMediaObject $social;
    /** @var NavObject[] */
    public array $nav_header = [];
    /** @var NavObject[] */
    public array $nav_footer = [];
    /** @var LanguageObject[] */
    public array $languages = [];

    public function __construct(
        Blog $blog,
        Language $language,
        PermalinkService $permalinkService,
    ) {
        $type = $blog->getType();
        $this->type = $type !== null ? $type->value : 'default';
        $this->subdomain = $blog->getSubdomain();

        $this->name = null;
        $this->description = null;
        foreach ($blog->getVariants() as $variant) {
            if ($variant->getLanguageId() === $language->getId()) {
                $this->name = $variant->getName();
                $this->description = $variant->getDescription();
                break;
            }
        }
        if ($this->name === null) {
            $first = $blog->getVariants()->first();
            if ($first) {
                $this->name = $first->getName();
                $this->description = $first->getDescription();
            }
        }

        $this->url = $permalinkService->getBlogPermalink($blog, $language);
        $this->base_url = $permalinkService->getBlogUrl($blog);

        $meta = $blog->getMeta() ?? [];
        $this->logo_url = is_string($meta['logo_url'] ?? null) ? $meta['logo_url'] : null;
        $this->icon_url = is_string($meta['icon_url'] ?? null) ? $meta['icon_url'] : null;
        $this->cover_url = is_string($meta['cover_url'] ?? null) ? $meta['cover_url'] : null;
        $this->code_head = is_string($meta['code_head'] ?? null) ? $meta['code_head'] : null;
        $this->code_foot = is_string($meta['code_foot'] ?? null) ? $meta['code_foot'] : null;
        $this->seo_indexing = (bool)($meta['seo_indexing'] ?? true);
        $this->seo_rich_schema = (bool)($meta['seo_rich_schema'] ?? false);
        $this->flashload = (bool)($meta['flashload'] ?? false);
        $this->color_modes = is_string($meta['color_modes'] ?? null) ? $meta['color_modes'] : 'light';
        $this->color_mode_default = is_string($meta['color_mode_default'] ?? null) ? $meta['color_mode_default'] : 'light';
        $this->cache_version_styles = is_numeric($meta['cache_version_styles'] ?? null) ? (int)$meta['cache_version_styles'] : 1;

        $this->social = new SocialMediaObject(
            is_string($meta['social_facebook'] ?? null) ? $meta['social_facebook'] : null,
            is_string($meta['social_twitter'] ?? null) ? $meta['social_twitter'] : null,
            is_string($meta['social_linkedin'] ?? null) ? $meta['social_linkedin'] : null,
            is_string($meta['social_youtube'] ?? null) ? $meta['social_youtube'] : null,
            is_string($meta['social_instagram'] ?? null) ? $meta['social_instagram'] : null,
            is_string($meta['social_github'] ?? null) ? $meta['social_github'] : null,
            is_string($meta['social_tiktok'] ?? null) ? $meta['social_tiktok'] : null,
        );

        $counts = $blog->getCounts() ?? [];
        $this->posts_count = is_numeric($counts['posts'] ?? null) ? (int)$counts['posts'] : 0;

        foreach ($blog->getNavigations() as $nav) {
            $navObj = new NavObject($nav, $language);
            if ($nav->getType() === NavigationType::HEADER) {
                $this->nav_header[] = $navObj;
            } else {
                $this->nav_footer[] = $navObj;
            }
        }

        foreach ($blog->getLanguages() as $lang) {
            $this->languages[] = new LanguageObject($lang);
        }
    }
}
