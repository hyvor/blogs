<?php
namespace App\Data\Objects\DataAPI;

use App\Models\Blog;

class BlogObject {

    public string $subdomain;
    public string $name;
    public string $description;
    public string $icon;
    public string $featured_image;
    public string $lang;
    public string $url;
    
    public BlogSocialObject $social;

    public array $nav_header;
    public array $nav_footer;

    public string $code_head;
    public string $code_foot;

    public int $posts_count;

    public function __construct(Blog $blog) {

        $this->subdomain = $blog->subdomain;
        $this->name = $blog->name;
        $this->description = $blog->description;
        $this->icon = $blog->icon;
        $this->featured_image = $blog->featured_image;
        
        $this->social = new BlogSocialObject($blog);

        // TODO: 

    }

}

class BlogSocialObject {

    public ?string $facebook;
    public ?string $twitter;
    public ?string $linkedin;
    public ?string $youtube;
    public ?string $instagram;
    public ?string $github;

    public function __construct(Blog $blog) {

        $this->facebook = $blog->social_facebook;
        $this->twitter = $blog->social_twitter;
        $this->linkedin = $blog->social_linkedin;
        $this->youtube = $blog->social_youtube;
        $this->instagram = $blog->social_instagram;
        $this->github = $blog->social_github;

    }

}