<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Enums\ColorModeDefaultEnum;
use App\Data\Enums\ColorModesEnum;
use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;

class BlogObject
{
    public string $subdomain;
    public string $name;
    public ?string $description;
    public ?string $logo_url;
    public ?string $cover_url;
    public string $lang;
    public string $url;
    public string $base_url;

    public SocialMediaObject $social;

    /**
     * @var NavObject[]
     */
    public array $nav_header = [];
    /**
     * @var NavObject[]
     */
    public array $nav_footer = [];

    /**
     * @var LanguageObject[]
     */
    public array $languages;

    public ?string $code_head;
    public ?string $code_foot;

    public int $posts_count;

    public ColorModesEnum $color_modes;
    public ColorModeDefaultEnum $color_mode_default;

    public function __construct(Blog $blog, Language $language)
    {
        $variants = $blog->variants;

        $this->subdomain = $blog->subdomain;
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language);
        $this->url = PermalinkRepository::getBlogPermalink($blog, $language);
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog, '');

        $this->icon_url = $blog->icon_url;
        $this->logo_url = $blog->logo_url ?? $blog->icon_url;
        $this->cover_url = $blog->cover_url;

        $meta = $blog->getAllMeta();

        $this->social = new SocialMediaObject(
            $meta->social_facebook,
            $meta->social_twitter,
            $meta->social_linkedin,
            $meta->social_youtube,
            $meta->social_instagram,
            $meta->social_github,
            $meta->social_tiktok
        );

        $this->color_modes = ColorModesEnum::from($meta->color_modes);
        $this->color_mode_default = ColorModeDefaultEnum::from($meta->color_mode_default);

        $this->code_head = $meta->code_head;
        $this->code_foot = $meta->code_foot;

        $blog->navigations->each(function ($nav) use ($language) {
            $navObject = new NavObject($nav, $language);
            if ($nav->type === NavigationTypeEnum::HEADER) {
                $this->nav_header[] = $navObject;
            } else {
                $this->nav_footer[] = $navObject;
            }
        });

        $this->languages = $blog->languages->mapInto(LanguageObject::class)->toArray();
    }
}
