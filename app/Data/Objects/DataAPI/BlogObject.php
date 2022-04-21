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

    public SocialMediaObject $social;

    public array $nav_header;
    public array $nav_footer;
    
    public LanguageObject $languageObject;

    public ?string $code_head;
    public ?string $code_foot;

    public int $posts_count;

    public function __construct(Blog $blog, Language $language)
    {
        
        $variants = $blog->variants;

        $this->subdomain = $blog->subdomain;
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language);
        $this->url = PermalinkRepository::getBlogPermalink($blog);
        
        $this->icon_url = $blog->icon;
        $this->featured_image_url = $blog->featured_image_url;

        $this->social = new SocialMediaObject(
            $blog->social_facebook,
            $blog->social_twitter,
            $blog->social_linkedin,
            $blog->social_youtube,
            $blog->social_instagram,
            $blog->social_github
        );

        $this->code_head = $blog->code_head;
        $this->code_foot = $blog->code_foot;

        // TODO:
    }

}
