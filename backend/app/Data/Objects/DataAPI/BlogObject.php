<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ColorModeDefaultEnum;
use App\Data\Enums\ColorModesEnum;
use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;

class BlogObject
{

    public BlogTypeEnum $type;

    public string $subdomain;

    public ?string $name;

    public ?string $description;

    public ?string $logo_url;

    public ?string $icon_url;

    public ?string $cover_url;

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

    public bool $seo_indexing;

    public bool $seo_rich_schema;

    public bool $flashload;

    public ColorModesEnum $color_modes;

    public ColorModeDefaultEnum $color_mode_default;

    public function __construct(Blog $blog, Language $language)
    {
        $variants = $blog->variants;

        $this->type = $blog->type;

        $this->subdomain = $blog->subdomain;
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language);
        $this->url = PermalinkRepository::getBlogPermalink($blog, $language);
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog, '');

        $meta = $blog->getAllMeta();

        $this->logo_url = $meta->logo_url;
        $this->icon_url = $meta->icon_url;
        $this->cover_url = $meta->cover_url;

        $this->social = new SocialMediaObject(
            $meta->social_facebook,
            $meta->social_twitter,
            $meta->social_linkedin,
            $meta->social_youtube,
            $meta->social_instagram,
            $meta->social_github,
            $meta->social_tiktok
        );

        $this->seo_indexing = $meta->seo_indexing;
        $this->seo_rich_schema = $meta->seo_rich_schema;
        $this->flashload = (bool) $meta->flashload;
        $this->color_modes = ColorModesEnum::from($meta->color_modes);
        $this->color_mode_default = ColorModeDefaultEnum::from($meta->color_mode_default);

        $this->code_head = $meta->code_head;
        $this->code_foot = $this->getFooterCode($blog, $meta->code_foot, $meta->hb_branding);

        $blog->navigations->each(function ($nav) use ($language) {
            $navObject = new NavObject($nav, $language);
            if ($nav->type === NavigationTypeEnum::HEADER) {
                $this->nav_header[] = $navObject;
            } else {
                $this->nav_footer[] = $navObject;
            }
        });

        $this->posts_count = $blog->getCount('posts');

        $this->languages = $blog->languages->mapInto(LanguageObject::class)->toArray();
    }


    private function getFooterCode(Blog $blog, ?string $codeFoot, ?bool $brandingMeta): ?string
    {

        $addBranding = $this->shouldAddBranding($blog, $brandingMeta);

        if (!$addBranding) {
            return $codeFoot;
        }

        $hbBranding = <<<HTML
<a href="https://blogs.hyvor.com?source=branding&subdomain=$blog->subdomain" target="_blank" style="position:fixed;bottom:15px;left:15px;font-size:12px;padding:6px 14px;background-color:#ececec;color:inherit;border-radius:20px;font-weight:600;z-index:10;text-decoration:none;line-height: 16px;">Published with Hyvor Blogs</a><style>a[href^="https://blogs.hyvor.com?source=branding"]:hover{opacity: 0.9;}.mode-dark a[href^="https://blogs.hyvor.com?source=branding"]{background-color:#2b2b2f!important}body{padding-bottom:25px;}</style>
HTML;

        return ($codeFoot ? $codeFoot . "\n" : '') . $hbBranding;

    }

    private function shouldAddBranding(Blog $blog, ?bool $brandingMeta) : bool
    {

        // no branding on dev and preview blogs
        if ($blog->type === BlogTypeEnum::DEV || $blog->type === BlogTypeEnum::PREVIEW) {
            return false;
        }

        // show by default
        if ($brandingMeta === null) {
            return true;
        }

        return $brandingMeta;

    }
}
