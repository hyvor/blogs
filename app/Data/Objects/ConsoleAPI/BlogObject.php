<?php
namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\CommentsTypeEnum;
use App\Models\Blog;
use App\Data\Objects\ConsoleAPI\BlogVariantObject;

class BlogObject {

    public ?int $id;
    public ?int $created_at;
    public ?int $updated_at;
    public ?string $subdomain;
    public ?string $icon_url;
    public ?string $featured_image_url; 

    public ?string $social_facebook;
    public ?string $social_twitter; 
    public ?string $social_linkedin; 
    public ?string $social_youtube; 
    public ?string $social_instagram;
    public ?string $social_github;

    public CommentsTypeEnum $comments_type;
    public ?int $comments_ht_website_id;
    public ?string $comments_ht_api_key;
    public ?string $comments_code;
    public ?string $newsletter_code;

    public function __construct(Blog $blog) {

        $this->id = $blog->id;
        $this->created_at = $blog->created_at->timestamp;
        $this->updated_at = $blog->updated_at->timestamp;        
        $this->subdomain = $blog->subdomain;

        $this->icon_url = $blog->icon_url  ;
        $this->featured_image_url = $blog->featured_image_url;

        $this->social_facebook = $blog->social_facebook;
        $this->social_twitter = $blog->social_twitter;
        $this->social_linkedin = $blog->social_linkedin;
        $this->social_youtube = $blog->social_youtube;
        $this->social_instagram = $blog->social_instagram;
        $this->social_github = $blog->social_github;
        
        $this->comments_type = CommentsTypeEnum::from($blog->comments_type);
        $this->comments_ht_website_id = $blog->comments_ht_website_id;
        $this->comments_api_key = $blog->comments_api_key;
        $this->comments_code = $blog->comments_code;
        $this->newsletter_code = $blog->newsletter_code;
        
        $this->variants = $blog->variants->map(function($variant) use ($blog) {
            return new BlogVariantObject($variant, $blog);
        })->keyBy('language_id');

    }

}