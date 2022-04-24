<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Navigation;

class NavigationObject
{
    public int $id;
    public int $uploaded_at;
    public int $blog_id;
    public string $name;
    public string $url;
    public string $type; 
    public ?int $sort; 

    public function __construct(Navigation $navigation)
    {
        $this->id = $navigation->id;
        $this->uploaded_at = $navigation->created_at->timestamp;
        $this->blog_id = $navigation->blog_id;
        $this->name = $navigation->name;
        $this->url = $navigation->url;
        $this->type = $navigation->type;
        $this->sort = $navigation->sort;
    }
}
