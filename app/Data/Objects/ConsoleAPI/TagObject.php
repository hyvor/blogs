<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Tag;

class TagObject
{
    public int $id;
    public int $created_at;
    public int $blog_id;
    public string $name;
    public string $slug;
    public ?string $description; 
    public ?string $featured_image; 
    public ?string $posts_count; 

    public function __construct(Tag $Tag)
    {
        $this->id = $Tag->id;
        $this->	created_at = $Tag->created_at->timestamp;
        $this->blog_id = $Tag->blog_id;
        $this->name = $Tag->name;
        $this->slug = $Tag->slug;
        $this->description = $Tag->description;
        $this->featured_image = $Tag->featured_image;
        $this->posts_count = $Tag->posts_count;
        
    }
} 
