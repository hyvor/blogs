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
    public ?string $posts_count; 
    public ?string $code_head; 
    public ?string $featured_image; 


    public function __construct(Tag $tag)
    {
        $this->id = $tag->id;
        $this->	created_at = $tag->created_at->timestamp;
        $this->blog_id = $tag->blog_id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->description = $tag->description;
        $this->posts_count = $tag->posts_count;
        $this->code_head = $tag->code_head;
        $this->code_foot = $tag->code_foot;
        
    }
} 
