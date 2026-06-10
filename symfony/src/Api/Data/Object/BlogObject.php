<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Enum\Blog\ColorMode;
use App\Entity\Enum\Blog\ColorModeDefault;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\NavigationType;
use App\Entity\Language;
use App\Service\Route\PermalinkService;

class BlogObject
{
    public BlogType $type;
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
    public ColorMode $color_modes;
    public ColorModeDefault $color_mode_default;
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
        $this->type = $blog->getType();
        $this->subdomain = $blog->getSubdomain();

        $chosenVariant = array_find($blog->getVariants()->toArray(), fn($variant) => $variant->getLanguage()->getId() === $language->getId()) ??
            ($blog->getVariants()->first() ?: null);

        $this->name = $chosenVariant?->getName() ?? null;
        $this->description = $chosenVariant?->getDescription() ?? null;

        $this->url = $permalinkService->getBlogPermalink($blog, $language);
        $this->base_url = $permalinkService->getBlogUrl($blog);

        $meta = $blog->getMeta();
        $this->logo_url = $meta->logo_url;
        $this->icon_url = $meta->icon_url;
        $this->cover_url = $meta->cover_url;
        $this->code_head = $meta->code_head;
        $this->code_foot = $meta->code_foot;
        $this->seo_indexing = $meta->seo_indexing;
        $this->seo_rich_schema = $meta->seo_rich_schema;
        $this->flashload = $meta->flashload;
        $this->color_modes = $meta->color_modes;
        $this->color_mode_default = $meta->color_mode_default;
        $this->cache_version_styles = $meta->cache_version_styles;

        $this->social = new SocialMediaObject(
            $meta->social_facebook,
            $meta->social_twitter,
            $meta->social_linkedin,
            $meta->social_youtube,
            $meta->social_instagram,
            $meta->social_github,
            $meta->social_tiktok,
        );

        $counts = $blog->getCounts() ?? [];
        $this->posts_count = is_numeric($counts['posts'] ?? null) ? (int) $counts['posts'] : 0;

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
