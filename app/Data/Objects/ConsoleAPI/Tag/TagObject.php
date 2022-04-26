<?php

namespace App\Data\Objects\ConsoleAPI\Tag;

use App\Models\Tag;
use App\Models\Blog;

class TagObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public int $blog_id;
    public string $slug;
    public ?string $posts_count; 
    public ?string $code_head;
    public ?string $featured_image;
    
    public array $variants;


    public function __construct(Tag $tag, Blog $blog)
    {
        $this->id = $tag->id;
        $this->created_at = $tag->created_at->timestamp;
        $this->updated_at = $tag->updated_at->timestamp;        
        $this->blog_id = $tag->blog_id;
        $this->slug = $tag->slug;
        $this->posts_count = $tag->posts_count;
        $this->code_head = $tag->code_head;
        $this->code_foot = $tag->code_foot;

        // variants
        // $this->variants = $tag->variants->map(function($variant) use ($blog, $tag) {
            // return new TagVariantObject($variant, $tag, $blog);
        
        $this->variants = $tag->variants->map(function($variant) use ($blog) {
            return new TagVariantObject($variant, $blog);
        })->keyBy('language_id')->toArray();
        
    }
} 
