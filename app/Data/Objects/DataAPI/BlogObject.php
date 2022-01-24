<?php

namespace App\Data\Objects\DataAPI;

use App\Models\Blog;

class BlogObject
{
    public string $subdomain;
    public string $name;
    public ?string $description;
    public ?string $icon;
    public ?string $featured_image;
    public string $lang;
    public string $url;

    public SocialMediaObject $social;

    public array $nav_header;
    public array $nav_footer;

    public string $code_head;
    public string $code_foot;

    public int $posts_count;

    public function __construct(Blog $blog)
    {

        $this->subdomain = $blog->subdomain;
        $this->name = $blog->name;
        $this->description = $blog->description;
        $this->icon = $blog->icon;
        $this->featured_image = $blog->featured_image;

        $this->social = new SocialMediaObject(
            $blog->social_facebook,
            $blog->social_twitter,
            $blog->social_linkedin,
            $blog->social_youtube,
            $blog->social_instagram,
            $blog->social_github
        );

        // TODO:
    }
}
