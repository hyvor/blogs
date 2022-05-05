<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;

class BlogObject
{
    public string $subdomain;
    public string $name;
    public ?string $description;
    public ?string $icon_url;
    public ?string $featured_image_url;
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

    public function __construct(Blog $blog, Language $language)
    {
        $variants = $blog->variants;

        $this->subdomain = $blog->subdomain;
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language);
        $this->url = PermalinkRepository::getBlogPermalink($blog, $language);
        $this->base_url = PermalinkRepository::getFullUrlFromPath($blog, '');

        $this->icon_url = $blog->icon;
        $this->featured_image_url = $blog->featured_image_url;


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

        $this->code_head = $blog->code_head;
        $this->code_foot = $blog->code_foot;

        $blog->navigations->each(function ($nav) {
            $navObject = new NavObject($nav);
            if ($nav->type === 'header') {
                $this->nav_header[] = $navObject;
            } else {
                $this->nav_footer[] = $navObject;
            }
        });

        $this->languages = $blog->languages->mapInto(LanguageObject::class)->toArray();
    }
}
