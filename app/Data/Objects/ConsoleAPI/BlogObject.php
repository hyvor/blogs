<?php
namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\ColorModeAllowedEnum;
use App\Data\Enums\ColorModeDefaultEnum;
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

    // meta
    public ?string $social_facebook;
    public ?string $social_twitter; 
    public ?string $social_linkedin; 
    public ?string $social_youtube; 
    public ?string $social_instagram;
    public ?string $social_github;
    
    public ?string $code_head;
    public ?string $code_foot;
    
    public bool $seo_indexing;
    public ?string $seo_robots_txt;
    public bool $seo_follow_external_links;

    public CommentsTypeEnum $comments_type;
    public ?int $comments_ht_website_id;
    public ?string $comments_ht_api_key;
    public ?string $comments_code;
    
    public ?string $newsletter_code;
    
    public ColorModeAllowedEnum $color_modes;
    public ColorModeDefaultEnum $color_mode_default;
    
    public bool $syntax_on;
    public bool $syntax_line_numbers;
    public ?string $syntax_theme;

    /**
     * @var array<int: BlogVariantObject>
     */
    public array $variants;

    public function __construct(Blog $blog) {

        $this->id = $blog->id;
        $this->created_at = $blog->created_at->timestamp;
        $this->updated_at = $blog->updated_at->timestamp;        
        $this->subdomain = $blog->subdomain;

        $this->icon_url = $blog->icon_url;
        $this->featured_image_url = $blog->featured_image_url;

        $meta = $blog->getAllMeta();

        $this->social_facebook = $meta->social_facebook;
        $this->social_twitter = $meta->social_twitter;
        $this->social_linkedin = $meta->social_linkedin;
        $this->social_youtube = $meta->social_youtube;
        $this->social_instagram = $meta->social_instagram;
        $this->social_github = $meta->social_github;
        
        $this->comments_type = CommentsTypeEnum::from($meta->comments_type);
        $this->comments_ht_website_id = $meta->comments_ht_website_id;
        $this->comments_ht_api_key = $meta->comments_ht_api_key;
        $this->comments_code = $meta->comments_code;
        
        $this->newsletter_code = $meta->newsletter_code;

        $this->seo_indexing = (bool) $meta->seo_indexing;
        $this->seo_robots_txt = $meta->seo_robots_txt;
        $this->seo_follow_external_links = (bool) $meta->seo_follow_external_links;

        $this->color_modes = ColorModeAllowedEnum::from($meta->color_modes);
        $this->color_mode_default = ColorModeDefaultEnum::from($meta->color_mode_default);
        
        $this->syntax_on = (bool) $meta->syntax_on;
        $this->syntax_line_numbers = (bool) $meta->syntax_line_numbers;
        $this->syntax_theme = $meta->syntax_theme;
        
        $this->variants = $blog->variants->map(function($variant) use ($blog) {
            return new BlogVariantObject($variant, $blog);
        })->keyBy('language_id');

    }

}
